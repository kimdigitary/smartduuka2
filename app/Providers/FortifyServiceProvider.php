<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\SyncTenantUsersToCentral;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Enums\AppID;
use App\Enums\ReservedTenantNames;
use App\Enums\Role;
use App\Enums\Status;
use App\Http\Requests\LoginValidationRequest;
use App\Models\CentralUser;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PinService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginRequest::class, LoginValidationRequest::class);

        $this->app->instance(LoginResponse::class, new class implements LoginResponse
        {
            public function toResponse($request): JsonResponse
            {
                $user = $request->user();
                $deviceId = $request->header('X-Device-Id', $request->ip());
                $tokenName = 'auth_token_'.$deviceId;

                if ($user instanceof CentralUser) {
                    return $this->centralAppLoginResponse($user, $tokenName);
                }

                return $this->tenantAppLoginResponse($user, $tokenName);
            }

            private function centralAppLoginResponse(CentralUser $user, string $tokenName): JsonResponse
            {
                $response = centralContext(function () use ($user, $tokenName) {
                    $user->tokens()->where('name', $tokenName)->delete();
                    $token = $user->createToken($tokenName);

                    if (! $user->is_reset && $user->force_reset) {
                        $user->withoutEvents(function () use ($user) {
                            $user->update([
                                'raw_pin' => app(PinService::class)->generateUniquePin(),
                            ]);
                        });

                        $user->refresh();
                    }

                    return response()->json([
                        'two_factor' => false,
                        'token' => $token->plainTextToken,
                        'user' => $user->makeHidden(['password', 'remember_token', 'pin'])->toArray(),
                    ]);
                });

                return $response instanceof JsonResponse
                    ? $response
                    : response()->json(['message' => 'Unable to complete central login.'], 500);
            }

            private function tenantAppLoginResponse(User $user, string $tokenName): JsonResponse
            {
                $tenantId = (string) $user->tenant_id;

                $response = tenantContext(function () use ($user, $tokenName, $tenantId) {
                    $tenantUser = $user->fresh() ?? $user;

                    $tenantUser->withoutEvents(function () use ($tenantUser, $tenantId) {
                        $updates = [
                            'last_login_date' => now(),
                            'tenant_id' => $tenantId,
                        ];

                        if (! $tenantUser->force_reset) {
                            $updates['raw_pin'] = null;
                        } elseif (! $tenantUser->is_reset) {
                            $updates['raw_pin'] = app(PinService::class)->generateUniquePin();
                        }

                        $tenantUser->update($updates);
                    });

                    $tenantUser->refresh();
                    $tenantUser->tokens()->where('name', $tokenName)->delete();
                    $token = $tenantUser->createToken($tokenName);

                    activityLog('Logged in', $tenantId, $tenantUser);
                    app(SyncTenantUsersToCentral::class)->sync();

                    $tenant = $tenantUser->tenant;

                    if ($tenant) {
                        $this->attachCentralUserToTenant($tenantUser, $tenantId);
                    }

                    return response()->json([
                        'two_factor' => false,
                        'token' => $token->plainTextToken,
                        'user' => $tenantUser->toArray(),
                        'tenant_id' => $tenantId,
                        'tenant_url' => $tenant?->frontend_url,
                        'tenant' => $tenant?->id,
                        'tenant_token' => $tenant?->token,
                    ]);
                }, $tenantId);

                return $response instanceof JsonResponse
                    ? $response
                    : response()->json(['message' => 'Unable to complete tenant login.'], 500);
            }

            private function attachCentralUserToTenant(User $tenantUser, string $tenantId): void
            {
                centralContext(function () use ($tenantUser, $tenantId) {
                    $centralUser = CentralUser::where(
                        $tenantUser->getGlobalIdentifierKeyName(),
                        $tenantUser->getGlobalIdentifierKey()
                    )->first();

                    if (! $centralUser) {
                        return;
                    }

                    $alreadyAttached = $centralUser->tenants()
                        ->where('tenants.id', $tenantId)
                        ->exists();

                    if (! $alreadyAttached) {
                        Pivot::withoutEvents(
                            fn () => $centralUser->tenants()->attach($tenantId)
                        );
                    }
                });
            }
        });
    }

    public function boot(PinService $pinService): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())).'|'.$request->ip()
            );

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::authenticateUsing(function (Request $request) use ($pinService) {
            $tenantId = $this->requestedTenantId($request);
            $centralUser = $this->resolveCentralUser($request, $pinService);

            if ($this->isCentralApp($tenantId)) {
                return $centralUser;
            }

            $tenant = $this->resolveLoginTenant($tenantId, $centralUser);

            if (! $tenant) {
                return null;
            }

            return tenantContext(function () use ($request, $pinService, $centralUser, $tenant, $tenantId) {
                $isSuperAdmin = $centralUser?->email === config('app.demo_email');
                $tenantUser = $centralUser
                    ? $this->resolveTenantUser($centralUser, $tenantId, $isSuperAdmin)
                    : $this->resolveUser($request, $pinService);

                if (! $tenantUser || ! $this->tenantUserCanUseApp($tenantUser, $tenantId, $isSuperAdmin)) {
                    return null;
                }

                $tenantUser->setAttribute('tenant_id', $tenant->getTenantKey());

                return $tenantUser;
            }, $tenant);
        });
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Authenticate against the central users table using pin or
     * email/phone + password. Always the source of truth for credentials.
     */
    private function resolveCentralUser(Request $request, PinService $pinService): ?CentralUser
    {
        if ($request->filled('pin')) {
            $pin = (string) $request->input('pin');

            return CentralUser::where(function ($query) use ($pinService, $pin) {
                $query->where('pin', $pinService->hashPin($pin))
                    ->orWhere('raw_pin', $pin);
            })
                ->where('status', Status::ACTIVE)
                ->first();
        }

        $login = $this->loginValue($request);
        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        /** @var CentralUser|null $user */
        $user = CentralUser::where($loginField, $login)
            ->where('status', Status::ACTIVE)
            ->first();

        if ($user && Hash::check((string) $request->input('password'), $user->password)) {
            return $user;
        }

        return null;
    }

    private function resolveUser(Request $request, PinService $pinService): ?User
    {
        if ($request->filled('pin')) {
            $pin = (string) $request->input('pin');

            return User::where(function ($query) use ($pinService, $pin) {
                $query->where('pin', $pinService->hashPin($pin))
                    ->orWhere('raw_pin', $pin);
            })
                ->where('status', Status::ACTIVE)
                ->first();
        }

        $login = $this->loginValue($request);
        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        /** @var User|null $user */
        $user = User::where($loginField, $login)
            ->where('status', Status::ACTIVE)
            ->first();

        if ($user && Hash::check((string) $request->input('password'), $user->password)) {
            return $user;
        }

        return null;
    }

    private function requestedTenantId(Request $request): ?string
    {
        $tenantId = $request->input('tenant_id');

        if ($tenantId === null && tenancy()->initialized) {
            $tenantId = tenancy()->tenant?->getTenantKey();
        }

        return filled($tenantId) ? (string) $tenantId : null;
    }

    private function loginValue(Request $request): string
    {
        return (string) ($request->input('email') ?? $request->input('phone') ?? '');
    }

    private function isCentralApp(?string $tenantId): bool
    {
        return $tenantId !== null
            && in_array($tenantId, ReservedTenantNames::toArray(), true);
    }

    private function resolveLoginTenant(?string $tenantId, ?CentralUser $centralUser): ?Tenant
    {
        if ($tenantId && ! $this->isCentralApp($tenantId)) {
            $tenant = Tenant::find($tenantId);

            if ($tenant || $centralUser?->email === config('app.demo_email')) {
                return $tenant;
            }
        }

        if ($centralUser?->tenant_id) {
            return Tenant::find($centralUser->tenant_id);
        }

        if (tenancy()->initialized) {
            return tenancy()->tenant;
        }

        return null;
    }

    /**
     * Check if a CentralUser holds Role::ADMIN in their home tenant.
     *
     * CentralUser is a plain Model with no Spatie roles — roles live on the
     * tenant-side User. We briefly peek into their home tenant DB to check.
     */
    private function centralUserIsAdmin(CentralUser $centralUser): bool
    {
        $homeTenant = Tenant::find($centralUser->tenant_id);

        if (! $homeTenant) {
            return false;
        }

        return tenantContext(
            fn () => $this->findTenantUserByCentralUser($centralUser)?->hasRole(Role::ADMIN) ?? false,
            $homeTenant
        );
    }

    /**
     * Resolve the tenant-side User and enforce app-level role restrictions.
     *
     * Super admin  → always allowed
     * Tenant admin → allowed everywhere including AppID::CASHFLOW
     * Regular user → blocked from AppID::CASHFLOW
     */
    private function resolveTenantUser(CentralUser $centralUser, ?string $appId, bool $isSuperAdmin): ?User
    {
        $tenantUser = $this->findTenantUserByCentralUser($centralUser);

        if (! $tenantUser) {
            return null;
        }

        if (! $this->tenantUserCanUseApp($tenantUser, $appId, $isSuperAdmin)) {
            return null;
        }

        return $tenantUser;
    }

    private function tenantUserCanUseApp(User $tenantUser, ?string $appId, bool $isSuperAdmin = false): bool
    {
        $isAdmin = $isSuperAdmin || $tenantUser->hasRole(Role::ADMIN);

        return $appId != AppID::CASHFLOW || $isAdmin;
    }

    /**
     * Low-level lookup: find a tenant User by global_id, falling back to email.
     * Repairs a missing global_id on the tenant record if the fallback is used.
     *
     * Must be called after tenancy is initialized for the target tenant.
     */
    private function findTenantUserByCentralUser(CentralUser $centralUser): ?User
    {
        $tenantUser = null;
        $globalIdentifier = $centralUser->getGlobalIdentifierKey();

        if ($globalIdentifier) {
            $tenantUser = User::where(
                $centralUser->getGlobalIdentifierKeyName(),
                $globalIdentifier
            )->first();
        }

        if ($tenantUser) {
            return $tenantUser;
        }

        $tenantUser = User::where('email', $centralUser->email)->first();

        if ($tenantUser) {
            $tenantUser->withoutEvents(function () use ($tenantUser, $centralUser) {
                $tenantUser->update([
                    'global_id' => $centralUser->getGlobalIdentifierKey(),
                ]);
            });
            $tenantUser->refresh();
        }

        return $tenantUser;
    }
}
