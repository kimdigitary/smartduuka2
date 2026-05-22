<?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Symfony\Component\HttpFoundation\Response;

    class SubscribedMiddleware
    {
        public function handle(Request $request , Closure $next) : Response
        {

            $tenantId  = tenant( 'id' );
            $branch_id = branchId();
            $cacheKey  = "tenant_subscription_{$tenantId}_{$branch_id}";

            $subscription = Cache::remember( $cacheKey , now()->addMinutes( 10 ) , function () use ($tenantId) {
                return tenantSubscriptions( $tenantId )->exists();
            } );

            if ( ! $subscription ) {
                return response()->json( [
                    'message' => 'Your subscription has expired. Please renew your subscription.'
                ] , 203 );
            }

            return $next( $request );
        }
    }
