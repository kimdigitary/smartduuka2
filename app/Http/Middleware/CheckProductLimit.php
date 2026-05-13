<?php

    namespace App\Http\Middleware;

    use App\Enums\PlanFeature;
    use App\Models\Product;
    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;

    class CheckProductLimit
    {
        public function handle(Request $request , Closure $next) : Response
        {
            $tenantId = $request->user()->tenant_id;
            $plan     = activeSubscription( $tenantId );

            if ( ! $plan ) {
                return $this->deny( 'No active subscription found.' );
            }

            $limit = match ( TRUE ) {
                $plan->hasFeature( PlanFeature::ITEMS_UNLIMITED ) => PHP_INT_MAX ,
                $plan->hasFeature( PlanFeature::ITEMS_500 )       => 500 ,
                $plan->hasFeature( PlanFeature::ITEMS_100 )       => 100 ,
                default                                           => 0 ,
            };

            $used = $this->countActiveProducts(  );

            if ( $used >= $limit ) {
                return $this->deny(
                    "You have reached your plan's limit of {$limit} products. Please upgrade."
                );
            }

            return $next( $request );
        }

        private function countActiveProducts() : int
        {
            return Product::count();
        }

        private function deny(string $message) : Response
        {
            return response()->json( [
                'status'  => FALSE ,
                'message' => $message ,
            ] , 403 );
        }
    }
