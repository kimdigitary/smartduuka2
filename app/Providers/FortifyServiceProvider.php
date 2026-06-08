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
        public function register() : void
        {
            $this->app->bind( LoginRequest::class , LoginValidationRequest::class );

            $this->app->instance( LoginResponse::class , new class implements LoginResponse {
                public function toResponse($request) : JsonResponse
                {
                    $user      = $request->user();
                    $deviceId  = $request->header( 'X-Device-Id' , $request->ip() );
                    $tokenName = 'auth_token_' . $deviceId;

                    if ( $user instanceof CentralUser ) {
                        return $this->centralAppLoginResponse( $user , $tokenName );
                    }
                    return $this->tenantAppLoginResponse( $user , $tokenName );
                }

                private function centralAppLoginResponse(CentralUser $user , string $tokenName) : JsonResponse
                {
                    return centralContext( function () use ($user , $tokenName) {
                        $user->tokens()->where( 'name' , $tokenName )->delete();
                        $token = $user->createToken( $tokenName );

                        tenancy()->end();

                        return response()->json( [
                            'two_factor' => FALSE ,
                            'token'      => $token->plainTextToken ,
                            'user'       => $user->toArray() ,
                        ] );

                    } );
                }

                private function tenantAppLoginResponse(User $user , string $tokenName) : JsonResponse
                {
                    return tenantContext( function () use ($user , $tokenName) {
                        $user->tokens()->where( 'name' , $tokenName )->delete();
                        $token = $user->createToken( $tokenName );
//                        tenancy()->end();
                        return response()->json( [
                            'two_factor'   => FALSE ,
                            'token'        => $token->plainTextToken ,
                            'user'         => $user->toArray() ,
                            'tenant_id'    => $user->tenant_id ,
                            'tenant_url'   => $user->tenant->frontend_url ,
                            'tenant'       => $user->tenant->id ,
                            'tenant_token' => $user->tenant->token ,
                        ] );
                    } , $user->tenant_id );
                }
            } );
        }

        public function boot(PinService $pinService) : void
        {
            Fortify::createUsersUsing( CreateNewUser::class );
            Fortify::updateUserProfileInformationUsing( UpdateUserProfileInformation::class );
            Fortify::updateUserPasswordsUsing( UpdateUserPassword::class );
            Fortify::resetUserPasswordsUsing( ResetUserPassword::class );
            Fortify::redirectUserForTwoFactorAuthenticationUsing( RedirectIfTwoFactorAuthenticatable::class );

            RateLimiter::for( 'login' , function (Request $request) {
                $throttleKey = Str::transliterate(
                    Str::lower( $request->input( Fortify::username() ) ) . '|' . $request->ip()
                );
                return Limit::perMinute( 5 )->by( $throttleKey );
            } );

            RateLimiter::for( 'two-factor' , function (Request $request) {
                return Limit::perMinute( 5 )->by( $request->session()->get( 'login.id' ) );
            } );

            Fortify::authenticateUsing( function (Request $request) use ($pinService) {

                // --------------------------------------------------------------
                // Step 1: Verify credentials against the central users table.
                //         Always the source of truth — pin or email/phone + password.
                // --------------------------------------------------------------
                $centralUser = $this->resolveCentralUser( $request , $pinService );
                if ( ! $centralUser ) {
                    return NULL;
                }

                $tenant_id = $request->string( 'tenant_id' );
                $isCentral = in_array( $tenant_id , ReservedTenantNames::toArray() );

                $appId        = $tenant_id;
                $isSuperAdmin = $centralUser->email === config( 'app.demo_email' );

                // --------------------------------------------------------------
                //          Allowed:  super admin
                //                    tenant admins
                // --------------------------------------------------------------
                if ( $isCentral ) {
//                    if ( ! $isSuperAdmin && ! $this->centralUserIsAdmin( $centralUser ) ) {
//                        return NULL;
//                    }

                    return $centralUser;
                }


                if ( $isSuperAdmin ) {
                    $tenant = Tenant::find( $tenant_id );
                }
                else {
                    $tenant = Tenant::find( $tenant_id ) ?? Tenant::find( $centralUser->tenant_id );
                }

                if ( ! $tenant ) {
                    return NULL;
                }

                tenancy()->initialize( $tenant );

                // --------------------------------------------------------------
                // Step 3: Find the matching tenant User and enforce app-level rules.
                //
                //         Super admin  → always allowed, skips all role checks
                //         Tenant admin → allowed everywhere including cashflow
                //         Regular user → allowed on tenant app, blocked on cashflow
                // --------------------------------------------------------------
                $tenantUser = $this->resolveTenantUser( $centralUser , $appId , $isSuperAdmin );
                if ( ! $tenantUser ) {
                    return NULL;
                }

                // --------------------------------------------------------------
                // Step 4: Housekeeping — last login, clear raw pin, sync to central.
                // --------------------------------------------------------------
                $tenantUser->withoutEvents( function () use ($tenantUser , $tenant) {
                    $updates = [
                        'last_login_date' => now() ,
                        'tenant_id'       => $tenant->id ,
                    ];

                    if ( ! $tenantUser->force_reset ) {
                        $updates[ 'raw_pin' ] = NULL;
                    }

                    $tenantUser->update( $updates );
                } );

                activityLog( 'Logged in' , $appId , $tenantUser );
                app( SyncTenantUsersToCentral::class )->sync();
                return $tenantUser;
            } );
        }

        // -------------------------------------------------------------------------
        // Private helpers
        // -------------------------------------------------------------------------

        /**
         * Authenticate against the central users table using pin or
         * email/phone + password. Always the source of truth for credentials.
         */
        private function resolveCentralUser(Request $request , PinService $pinService) : ?CentralUser
        {
            if ( $request->filled( 'pin' ) ) {
                $pin = $request->string( 'pin' );

                return CentralUser::where( 'pin' , $pinService->hashPin( $pin ) )
                                  ->orWhere( 'raw_pin' , $pin )
                                  ->first();
            }

            $loginField = filter_var( $request->email , FILTER_VALIDATE_EMAIL ) ? 'email' : 'phone';

            /** @var CentralUser|null $user */
            $user = CentralUser::where( $loginField , $request->email )
                               ->where( 'status' , Status::ACTIVE )
                               ->first();

            if ( $user && Hash::check( $request->password , $user->password ) ) {
                return $user;
            }

            return NULL;
        }

        /**
         * Check if a CentralUser holds Role::ADMIN in their home tenant.
         *
         * CentralUser is a plain Model with no Spatie roles — roles live on the
         * tenant-side User. We briefly peek into their home tenant DB to check.
         */
        private function centralUserIsAdmin(CentralUser $centralUser) : bool
        {
            $homeTenant = Tenant::find( $centralUser->tenant_id );

            if ( ! $homeTenant ) {
                return FALSE;
            }

            return tenantContext(
                fn() => $this->findTenantUserByCentralUser( $centralUser )?->hasRole( Role::ADMIN ) ?? FALSE ,
                $homeTenant
            );
        }

        /**
         * Resolve the tenant-side User and enforce app-level role restrictions.
         *
         * Super admin  → always allowed, pivot attachment skipped (not tied to any tenant)
         * Tenant admin → allowed everywhere including AppID::CASHFLOW
         * Regular user → blocked from AppID::CASHFLOW
         */
        private function resolveTenantUser(CentralUser $centralUser , ?string $appId , bool $isSuperAdmin) : ?User
        {
            $tenantUser = $this->findTenantUserByCentralUser( $centralUser );

            if ( ! $tenantUser ) {
                return NULL;
            }

            $isAdmin = $isSuperAdmin || $tenantUser->hasRole( Role::ADMIN );

            // Cashflow is admin-only — block regular tenant users
            if ( $appId == AppID::CASHFLOW && ! $isAdmin ) {
                return NULL;
            }

            // Record the central user ↔ tenant relationship in the pivot table.
            // Skipped for super admin — they are not semantically tied to any tenant.
            if ( ! $isSuperAdmin ) {
                $this->attachCentralUserToTenant( $centralUser );
            }

            return $tenantUser;
        }

        /**
         * Attach the CentralUser to the current tenant via the tenant_users pivot.
         *
         * The pivot table lives in the central DB, so we must switch context.
         * Uses withoutEvents to avoid triggering a redundant ResourceSyncing cascade
         * on every login — the user is already synced, we're only recording the link.
         */
        private function attachCentralUserToTenant(CentralUser $centralUser) : void
        {
            $currentTenantId = tenancy()->tenant->getTenantKey();

            tenancy()->central( function () use ($centralUser , $currentTenantId) {
                $alreadyAttached = $centralUser->tenants()
                                               ->where( 'tenants.id' , $currentTenantId )
                                               ->exists();

                if ( ! $alreadyAttached ) {
                    Pivot::withoutEvents(
                        fn() => $centralUser->tenants()->attach( $currentTenantId )
                    );
                }
            } );
        }

        /**
         * Low-level lookup: find a tenant User by global_id, falling back to email.
         * Repairs a missing global_id on the tenant record if the fallback is used.
         *
         * Must be called after tenancy is initialized for the target tenant.
         */
        private function findTenantUserByCentralUser(CentralUser $centralUser) : ?User
        {
//            return tenantContext( function () use ($centralUser) {
            $tenantUser = User::where(
                $centralUser->getGlobalIdentifierKeyName() ,
                $centralUser->getGlobalIdentifierKey()
            )->first();

            if ( $tenantUser ) {
                return $tenantUser;
            }

            $tenantUser = User::where( 'email' , $centralUser->email )->first();

            if ( $tenantUser ) {
                $tenantUser->withoutEvents( function () use ($tenantUser , $centralUser) {
                    $tenantUser->update( [
                        'global_id' => $centralUser->getGlobalIdentifierKey() ,
                    ] );
                } );
                $tenantUser->refresh();
            }

            return $tenantUser;
//            } , $centralUser->tenant_id );
        }
    }