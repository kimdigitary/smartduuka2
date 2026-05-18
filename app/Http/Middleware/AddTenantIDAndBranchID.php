<?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;

    class AddTenantIDAndBranchID
    {
        public function handle(Request $request , Closure $next) : Response
        {
            $tenantId = $request->header( 'X-TenantId' );
            $branchId = $request->header( 'X-BranchId' );

            if ( ! $request->has( 'tenant_id' ) ) {
                $request->merge( [ 'tenant_id' => $tenantId ] );
            }
            if ( ! $request->has( 'branch_id' ) ) {
                $request->merge( [ 'branch_id' => $branchId ] );
            }
            return $next( $request );
        }
    }