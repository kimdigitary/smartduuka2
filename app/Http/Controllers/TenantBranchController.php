<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\TenantBranchRequest;
    use App\Http\Resources\TenantBranchResource;
    use App\Models\TenantBranch;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class TenantBranchController extends Controller
    {
        public function index(Request $request)
        {
            $status = $request->integer( 'status' );
            return TenantBranchResource::collection( TenantBranch::when( $status, fn($q) => $q->where( 'status' , $status ) )
                                                                 ->latest()->get() );
        }

        public function store(TenantBranchRequest $request , TenantSubscriptionController $tenantSubscriptionController)
        {
            try {
                return DB::transaction( function () use ($request , $tenantSubscriptionController) {
                    $branch  = TenantBranch::create( $request->validated() );
                    $payment = json_decode( $request->input( 'payment' ) , TRUE );

                    $branch->update( [ 'code' => recordId( 'BR' , $branch , 3 ) ] );

                    $payment[ 'branch_id' ] = $branch->id;

                    return $tenantSubscriptionController->createSubscription( $payment );
                } );
            } catch ( \Throwable $e ) {
                return response( [ 'status' => FALSE , 'message' => $e->getMessage() ] , 422 );
            }
        }

        public function show(TenantBranch $tenantBranch)
        {
            return new TenantBranchResource( $tenantBranch );
        }

        public function update(TenantBranchRequest $request , TenantBranch $tenantBranch)
        {
            //            tenancy()->central( fn() => $tenantBranch->update( $request->validated() ) );
            $tenantBranch->update( $request->validated() );

            return response()->json();
        }

        public function destroy(Request $request)
        {
            //            tenancy()->central( fn() => TenantBranch::destroy( $request->ids ) );
            TenantBranch::destroy( $request->ids );

            return response()->json();
        }
    }
