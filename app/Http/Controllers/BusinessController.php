<?php

    namespace App\Http\Controllers;

    use App\Enums\Status;
    use App\Http\Resources\BusinessResource;
    use App\Models\BusinessOnBoard;
    use App\Traits\HasAdvancedFilter;
    use Illuminate\Http\Request;

    class BusinessController extends Controller
    {
        use HasAdvancedFilter;

        public function index(Request $request)
        {
            $user     = $request->user();
            $onboards = BusinessOnBoard::with(
                [
                    'business.activeSubscriptions.subscriptionPlan' ,
                    'business.activeSubscriptions.billingCycle' ,
                    'business.branches.activeSubscriptions.subscriptionPlan' ,
                    'business.branches.activeSubscriptions.billingCycle'
                ] )
                                       ->where( [
                                           'admin_email' => $user?->email ,
                                           'status'      => Status::ACTIVE ,
                                       ] )->get();
//            info( $onboards );
//            $query    = Tenant::with( [ 'domains' , 'activeSubscriptions' ] );
            return BusinessResource::collection( $onboards );
        }

        public function store(Request $request)
        {
            //
        }

        public function show(string $id)
        {
            //
        }

        public function update(Request $request , string $id)
        {
            //
        }

        public function destroy(string $id)
        {
            //
        }
    }
