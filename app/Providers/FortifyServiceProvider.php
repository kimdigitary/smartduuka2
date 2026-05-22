<?php

    namespace App\Providers;

    use App\Actions\Fortify\CreateNewUser;
    use App\Actions\Fortify\ResetUserPassword;
    use App\Actions\Fortify\SyncTenantUsersToCentral;
    use App\Actions\Fortify\UpdateUserPassword;
    use App\Actions\Fortify\UpdateUserProfileInformation;
    use App\Enums\AppID;
    use App\Enums\Role;
    use App\Enums\Status;
    use App\Models\CentralPersonalAccessToken;
    use App\Models\CentralUser;
    use App\Models\Tenant;
    use App\Models\TenantPersonalAccessToken;
    use App\Models\User;
    use App\Services\PinService;
    use Illuminate\Cache\RateLimiting\Limit;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\RateLimiter;
    use Illuminate\Support\Facades\Validator;
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
            $this->app->bind( LoginRequest::class , function () {
                return new class extends LoginRequest {
                    public function rules() : array
                    {
                        return [
                            'email'    => [ 'required_without:pin' , 'email' ] ,
                            'password' => [ 'required_with:email' , 'string' ] ,
                            'pin'      => [ 'required_without:email' , 'string' ] ,
                        ];
                    }
                };
            } );
            $this->app->instance( LoginResponse::class , new class implements LoginResponse {
                public function toResponse($request) : JsonResponse
                {
                    $user      = $request->user();
                    $deviceId  = $request->header( 'X-Device-Id' , $request->ip() );
                    $tokenName = 'auth_token_' . $deviceId;

                    $user->tokens()->where( 'name' , $tokenName )->delete();

                    $rawToken = \Illuminate\Support\Str::random( 40 );
                    $globalId = (string) \Illuminate\Support\Str::uuid();

                    /** @var \App\Models\TenantPersonalAccessToken $tenantToken */
                    $tenantToken = \App\Models\TenantPersonalAccessToken::withoutEvents( function () use (
                        $user , $tokenName , $rawToken , $globalId
                    ) {
                        $token = new \App\Models\TenantPersonalAccessToken();
                        $token->forceFill( [
                            'tokenable_type' => get_class( $user ) ,
                            'tokenable_id'   => $user->getKey() ,
                            'name'           => $tokenName ,
                            'token'          => hash( 'sha256' , $rawToken ) ,
                            'abilities'      => [ '*' ] ,
                            'global_id'      => $globalId ,
                        ] );
                        $token->save();
                        return $token;
                    } );

                    $plainText = $tenantToken->getKey() . '|' . $rawToken;

                    $this->syncTokenToCentral( $tenantToken , $globalId , $user );

                    return response()->json( [
                        'two_factor'   => FALSE ,
                        'token'        => $plainText ,
                        'user'         => $user->toArray() ,
                        'tenant_id'    => $user->tenant_id ,
                        'redirect_url' => $user->tenant->frontend_url . '/auto-login?token=' . $plainText ,
                        'tenant_url'   => $user->tenant->frontend_url ,
                        'tenant'       => $user->tenant->id ,
                        'tenant_token' => $user->tenant->token ,
                    ] );
                }

                private function syncTokenToCentral(
                    TenantPersonalAccessToken $tenantToken ,
                    string $globalId ,
                    User $tenantUser
                ) : void
                {
                    // We need the central user's ID for the tokenable_id column so that
                    // Sanctum resolves the correct CentralUser when authenticating on
                    // central routes.
                    $centralUser = CentralUser::where( 'global_id' , $tenantUser->global_id )->first();
                    if ( ! $centralUser ) {
                        // Fallback: match by email (handles edge-cases before global_id is set)
                        $centralUser = CentralUser::where( 'email' , $tenantUser->email )->first();
                    }

                    if ( ! $centralUser ) {
                        // Cannot sync without a matching central user — token will only work on tenant
                        return;
                    }

                    $currentTenantId = tenancy()->tenant->getTenantKey();

                    tenancy()->central( function () use (
                        $tenantToken , $globalId , $centralUser , $currentTenantId
                    ) {
                        $central = CentralPersonalAccessToken::withoutEvents(
                            fn() => CentralPersonalAccessToken::updateOrCreate(
                                [ 'global_id' => $globalId ] ,
                                [
                                    'tokenable_type' => CentralUser::class ,
                                    'tokenable_id'   => $centralUser->id ,
                                    'name'           => $tenantToken->name ,
                                    'token'          => $tenantToken->token ,
                                    // FIX: Removed json_encode() to prevent double-encoding the JSON array
                                    'abilities'      => $tenantToken->abilities ?? [ '*' ] ,
                                    'expires_at'     => $tenantToken->expires_at ,
                                ]
                            )
                        );

                        // Attach pivot if not already present
                        $alreadyAttached = $central->tenants()
                                                   ->where( 'tenants.id' , $currentTenantId )
                                                   ->exists();
                        if ( ! $alreadyAttached ) {
                            \Illuminate\Database\Eloquent\Relations\Pivot::withoutEvents(
                                fn() => $central->tenants()->attach( $currentTenantId )
                            );
                        }
                    } );
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
                $throttleKey = Str::transliterate( Str::lower( $request->input( Fortify::username() ) ) . '|' . $request->ip() );

                return Limit::perMinute( 5 )->by( $throttleKey );
            } );

            RateLimiter::for( 'two-factor' , function (Request $request) {
                return Limit::perMinute( 5 )->by( $request->session()->get( 'login.id' ) );
            } );

            Fortify::authenticateUsing( function (Request $request) use ($pinService) {
                $centralUser = NULL;

                if ( $request->filled( 'pin' ) ) {
                    $pin       = $request->string( 'pin' );
                    $validator = Validator::make( $request->only( 'pin' ) , [ 'pin' => 'required|string|size:5' ] );
                    if ( $validator->fails() ) return NULL;

                    $centralUser = CentralUser::where( 'pin' , $pinService->hashPin( $pin ) )
                                              ->orWhere( 'raw_pin' , $pin )
                                              ->first();
                }
                else {
                    $loginField = filter_var( $request->email , FILTER_VALIDATE_EMAIL ) ? 'email' : 'phone';

                    $validator = Validator::make( $request->all() , [
                        $loginField => 'required|string' ,
                        'password'  => 'required|string' ,
                    ] );
                    if ( $validator->fails() ) return NULL;

                    $user = CentralUser::where( $loginField , $request->email )
                                       ->where( 'status' , Status::ACTIVE )
                                       ->first();

                    if ( $user && Hash::check( $request->password , $user->password ) ) {
                        $centralUser = $user;
                    }
                }

                if ( ! $centralUser ) return NULL;

                $host       = $request->getHost();
                $subdomain  = explode( '.' , $host )[ 0 ];
                $tenantSlug = Str::before( $subdomain , '-api' );

                $tenant = Tenant::where( 'id' , $tenantSlug )
                                ->orWhere( 'id' , $centralUser->tenant_id )
                                ->first();

                if ( ! $tenant ) return NULL;

                tenancy()->initialize( $tenant );

                $app_id = $request->header( 'X-App-Id' );

                $tenantUser = User::where(
                    $centralUser->getGlobalIdentifierKeyName() ,
                    $centralUser->getGlobalIdentifierKey()
                )->when( $app_id == AppID::CASHFLOW , fn($q) => $q->role( Role::ADMIN ) )
                                  ->first();

                if ( ! $tenantUser ) {
                    $tenantUser = User::where( 'email' , $centralUser->email )->first();

                    if ( $tenantUser ) {
                        $tenantUser->withoutEvents( function () use ($tenantUser , $centralUser) {
                            $tenantUser->update( [
                                'global_id' => $centralUser->getGlobalIdentifierKey() ,
                            ] );
                        } );
                        $tenantUser->refresh();
                    }
                }

                if ( ! $tenantUser ) return NULL;

                $tenantUser->withoutEvents( function () use ($tenantUser , $tenant) {
                    $tenantUser->update( [
                        'last_login_date' => now() ,
                        'tenant_id'       => $tenant->id ,
                    ] );
                    if ( ! $tenantUser->force_reset ) {
                        $tenantUser->update( [
                            'raw_pin' => NULL ,
                        ] );
                    }
                } );

                activityLog( 'Logged in' , $app_id , $tenantUser );
                app( SyncTenantUsersToCentral::class )->sync();
                return $tenantUser;
            } );
        }
    }
