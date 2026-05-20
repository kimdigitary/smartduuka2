<?php

    namespace App\Http\Controllers;

    use App\Enums\Status;
    use App\Enums\SystemPaymentType;
    use App\Http\Requests\TenantSubscriptionRequest;
    use App\Http\Resources\TenantSubscriptionResource;
    use App\Jobs\InitiatePaymentJob;
    use App\Models\BillingCycle;
    use App\Models\PaymentTransaction;
    use App\Models\TenantSubscription;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class TenantSubscriptionController extends Controller
    {
        public function index(Request $request)
        {
            $page     = $request->integer( 'page' );
            $per_page = $request->integer( 'per_page' );
            $tenant   = $request->string( 'tenant_id' );

            $query = TenantSubscription::with( [ 'billingCycle' , 'subscriptionPlan' ] )
                                       ->where( 'tenant_id' , $tenant )
                                       ->latest();

            $subscriptions = $query->paginate( $per_page , [ '*' ] , 'page' , $page );
            return TenantSubscriptionResource::collection( $subscriptions );
        }

        public function store(TenantSubscriptionRequest $request)
        {
            try {
                return $this->createSubscription( $request->validated() );
            } catch ( \Throwable $e ) {
                return response( [ 'status' => FALSE , 'message' => $e->getMessage() ] , 422 );
            }
        }

        /**
         * @throws \Throwable
         */
        public function createSubscription(array $data)
        {
            return DB::transaction( function () use ($data) {
                $transaction = PaymentTransaction::create( [
                    'amount'         => $data[ 'amount' ] ,
                    'transaction_id' => $data[ 'transaction_id' ] ,
                    'payment_type'   => SystemPaymentType::SUBSCRIPTION ,
                ] );

                $subscription = TenantSubscription::create( [
                    'phone'                => $data[ 'phone' ] ,
                    'amount'               => $data[ 'amount' ] ,
                    'branch_id'            => $data[ 'branch_id' ] ,
                    'billing_cycle_id'     => $data[ 'billingCycle' ] ,
                    'tenant_id'            => $data[ 'tenant' ] ,
                    'subscription_plan_id' => $data[ 'subscriptionPlan' ] ,
                    'status'               => Status::INACTIVE ,
                ] );

                $cycle = BillingCycle::find( $data[ 'billingCycle' ] );

                $activeSubscription = tenantSubscriptions( $data[ 'tenant' ] )->first();
                $expiryBase         = $activeSubscription ? $activeSubscription->expires_at : now();

                $subscription->update( [
                    'invoice_no' => recordId( 'INV' , $subscription ) ,
                    'expires_at' => $expiryBase->addMonths( $cycle->multiplier ) ,
                ] );

                InitiatePaymentJob::dispatch( $transaction );

                return response()->json();
            } );
        }

        public function destroy(TenantSubscription $tenantSubscription)
        {
            $tenantSubscription->delete();

            return response()->json();
        }
    }
