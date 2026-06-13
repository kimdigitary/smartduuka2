<?php

    namespace App\Http\Middleware;

    use App\Enums\PlanFeature;
    use App\Enums\Role;
    use App\Enums\Status;
    use App\Models\User;
    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;

    class CheckUsersLimit
    {
        public function handle(Request $request , Closure $next) : Response
        {
            $tenantId = tenant( 'id' );
            $plan     = activeSubscription( $tenantId );

            if ( ! $plan ) {
                return $this->deny( 'No active subscription found.' );
            }

            $limit = match ( TRUE ) {
                $plan->hasFeature( PlanFeature::USERS_UNLIMITED ) => PHP_INT_MAX ,
                $plan->hasFeature( PlanFeature::USERS_5 )         => 5 ,
                $plan->hasFeature( PlanFeature::USERS_3 )         => 3 ,
                default                                           => 0 ,
            };

            $used = $this->countActiveUsers();

            if ( $used >= $limit ) {
                return $this->deny(
                    "You have reached your plan's limit of {$limit} users. Please upgrade."
                );
            }

            return $next( $request );
        }

        private function countActiveUsers() : int
        {
            return User::withoutRole( [ Role::CUSTOMER ] )
                       ->where( 'status' , Status::ACTIVE )
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