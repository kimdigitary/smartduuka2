<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\TenantBranchRequest;
    use App\Http\Resources\TenantBranchResource;
    use App\Models\TenantBranch;
    use Illuminate\Http\Request;

    class TenantBranchController extends Controller
    {
        public function index()
        {
            return tenancy()->central( fn() => TenantBranchResource::collection( TenantBranch::all() ) );
//            return TenantBranchResource::collection( TenantBranch::all() );
        }

        public function store(TenantBranchRequest $request)
        {
            tenancy()->central( function () use ($request) {
                $tenant = TenantBranch::create( $request->validated() );
                $tenant->update( [ 'code' => recordId( 'BR' , $tenant , 3 ) ] );
            } );
            return response()->json();
        }

        public function show(TenantBranch $tenantBranch)
        {
            return new TenantBranchResource( $tenantBranch );
        }

        public function update(TenantBranchRequest $request , TenantBranch $tenantBranch)
        {
            tenancy()->central( fn() => $tenantBranch->update( $request->validated() ) );

            return response()->json();
        }

        public function destroy(Request $request)
        {
            info($request->all());
            tenancy()->central( fn() => TenantBranch::destroy( $request->ids ) );

            return response()->json();
        }
    }
