<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\TenantBranchRequest;
    use App\Http\Resources\TenantBranchResource;
    use App\Models\TenantBranch;

    class TenantBranchController extends Controller
    {
        public function index()
        {
            return TenantBranchResource::collection( TenantBranch::all() );
        }

        public function store(TenantBranchRequest $request)
        {
            $tenant = TenantBranch::create( $request->validated() );
            $tenant->update( [ 'code' => recordId( 'BR' , $tenant,3 ) ] );
            return response()->json();
        }

        public function show(TenantBranch $tenantBranch)
        {
            return new TenantBranchResource( $tenantBranch );
        }

        public function update(TenantBranchRequest $request , TenantBranch $tenantBranch)
        {
            $tenantBranch->update( $request->validated() );

            return new TenantBranchResource( $tenantBranch );
        }

        public function destroy(TenantBranch $tenantBranch)
        {
            $tenantBranch->delete();

            return response()->json();
        }
    }
