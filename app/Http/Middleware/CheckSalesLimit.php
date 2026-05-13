<?php

    namespace App\Http\Middleware;

    use App\Enums\PlanFeature;
    use App\Models\Order;
    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;

    class CheckSalesLimit
    {
        public function handle(Request $request , Closure $next) : Response
        {
            $tenantId = $request->user()->tenant_id;
            $plan     = activeSubscription( $tenantId );

            if ( ! $plan ) {
                return $this->deny( 'No active subscription found.' );
            }

            $limit = match ( TRUE ) {
                $plan->hasFeature( PlanFeature::SALES_UNLIMITED ) => PHP_INT_MAX ,
                $plan->hasFeature( PlanFeature::SALES_2000 )      => 2000 ,
                $plan->hasFeature( PlanFeature::SALES_300 )       => 300 ,
                default                                           => 0 ,
            };

            $used = $this->countOrdersThisMonth(  );

            if ( $used >= $limit ) {
                return $this->deny(
                    "You have reached your plan's limit of {$limit} sales/mo. Please upgrade."
                );
            }

            return $next( $request );
        }

        private function countOrdersThisMonth() : int
        {
            return Order::whereMonth( 'created_at' , now()->month )
                        ->whereYear( 'created_at' , now()->year )
                        ->count();
        }

        private function deny(string $message) : Response
        {
            return response()->json( [
                'status'  => FALSE ,
                'message' => $message ,
            ] , 403 );
        }
    }