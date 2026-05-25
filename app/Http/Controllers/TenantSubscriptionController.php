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
    use Smartisan\Settings\Facades\Settings;

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
                return $this->createSubscription( array_merge( $request->validated() , [ 'type' => SystemPaymentType::SUBSCRIPTION ] ) );
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

                $subscription = TenantSubscription::create( [
                    'phone'                => $data[ 'phone' ] ,
                    'amount'               => $data[ 'amount' ] ,
                    'branch_id'            => $data[ 'branch_id' ] ,
                    'billing_cycle_id'     => $data[ 'billingCycle' ] ,
                    'tenant_id'            => $data[ 'tenant' ] ,
                    'subscription_plan_id' => $data[ 'subscriptionPlan' ] ,
                    'status'               => Status::INACTIVE ,
                ] );

                $company = tenantContext( fn() => Settings::group( 'company' )->get() , tenantId() );

                $transaction = PaymentTransaction::create( [
                    'amount'           => $data[ 'amount' ] ,
                    'phone'            => $data[ 'phone' ] ,
                    'data'             => [
                        'email'         => $data[ 'email' ] ,
                        'modules'       => $data[ 'modules' ] ,
                        'business_name' => data_get( $company , 'company_name' )
                    ] ,
                    'payment_type'     => $data[ 'type' ] ,
                    'payment_type_id'  => $subscription->id ,
                    'tenant_branch_id' => $data[ 'branch_id' ] ,
                    'tenant_id'        => tenantId() ,
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
