<?php

    namespace App\Http\Middleware;

    use App\Models\CentralPersonalAccessToken;
    use App\Models\TenantPersonalAccessToken;
    use Closure;
    use Illuminate\Http\Request;
    use Laravel\Sanctum\Sanctum;
    use Symfony\Component\HttpFoundation\Response;

    class ConfigureSanctumTokenModel
    {

        public function handle(Request $request , Closure $next) : Response
        {
            if ( tenancy()->initialized ) {
                Sanctum::usePersonalAccessTokenModel( TenantPersonalAccessToken::class );
            }
            else {
                Sanctum::usePersonalAccessTokenModel( CentralPersonalAccessToken::class );
            }

            return $next( $request );
        }
    }
