<?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;

    class DynamicSanctumConfiguration
    {

        public function handle(Request $request , Closure $next) : Response
        {
            if ( tenancy()->initialized ) {
                config( [
                    'auth.guards.sanctum.provider' => 'users' ,
                    'sanctum.guard'                => [ 'web' ] ,
                ] );
            }
            else {
                config( [
                    'auth.guards.sanctum.provider' => 'central_users' ,
                    'sanctum.guard'                => [ 'central' ] ,
                ] );
            }
            return $next( $request );
        }
    }
