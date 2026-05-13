<?php

    namespace App\Http\Middleware;

    use App\Enums\PlanFeature;
    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;

    class RequireFeature
    {
        public function handle(Request $request , Closure $next , string ...$features) : Response
        {
            $tenantId = $request->user()->tenant_id;

            // Pass if the tenant has ANY of the required features
            $allowed = collect( $features )->some(
                fn(string $f) => hasFeature( $tenantId , PlanFeature::from( $f ) )
            );

            if ( ! $allowed ) {
                return response()->json( [
                    'message' => 'Your plan does not include this feature.' ,
                ] , 403 );
            }

            return $next( $request );
        }
    }