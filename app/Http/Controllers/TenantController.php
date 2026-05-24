<?php

    namespace App\Http\Controllers;

    use App\Enums\Status;
    use App\Enums\SystemPaymentType;
    use App\Http\Requests\TenantRequest;
    use App\Http\Resources\TenantResource;
    use App\Jobs\InitiatePaymentJob;
    use App\Models\BillingCycle;
    use App\Models\BusinessOnBoard;
    use App\Models\PaymentTransaction;
    use App\Models\Tenant;
    use App\Models\TenantBranch;
    use App\Models\TenantSubscription;
    use Illuminate\Support\Facades\DB;

    class TenantController extends Controller
    {
        public function index()
        {
            return TenantResource::collection( Tenant::all() );
        }

        public function show(Tenant $tenant)
        {
            return new TenantResource( $tenant );
        }

        public function store(TenantRequest $request)
        {
            try {
                return DB::transaction( function () use ($request) {
                    $data = $request->validated();

                    if ( isDev() )
                        $data[ 'amountPaid' ] = 1000;

                    BusinessOnBoard::updateOrCreate(
                        [
                            'status'      => Status::INACTIVE->value ,
                            'admin_email' => $data[ 'adminEmail' ] ,
                            'tenant'      => $data[ 'tenant' ]
                        ] ,
                        [
                            'address'             => $data[ 'businessAddress' ] ,
                            'admin_email'         => $data[ 'adminEmail' ] ,
                            'admin_name'          => $data[ 'adminName' ] ,
                            'admin_password'      => $data[ 'adminPassword' ] ,
                            'admin_pin'           => $data[ 'adminPin' ] ?? 123456 ,
                            'amount'              => $data[ 'amountPaid' ] ,
                            'cycle_id'            => $data[ 'billingCycleId' ] ,
                            'email'               => $data[ 'businessEmail' ] ,
                            'mobile_phone_number' => $data[ 'mobileMoneyNumber' ] ,
                            'name'                => $data[ 'businessName' ] ,
                            'payment_method'      => $data[ 'paymentMethod' ] ,
                            'phone'               => $data[ 'businessPhone' ] ,
                            'plan_id'             => $data[ 'subscriptionPlanId' ] ,
                            'tenant'              => $data[ 'tenant' ] ,
                        ] );


                    $tenant_id = $data[ 'tenant' ];

                    $branch = TenantBranch::updateOrCreate(
                        [
                            'tenant_id' => $tenant_id ,
                            'name'      => 'Main Branch' ,
                        ] ,
                        [
                            'email'      => $data[ 'businessEmail' ] ,
                            'address'    => $data[ 'businessAddress' ] ,
                            'phone'      => $data[ 'businessPhone' ] ,
                            'phone2'     => $data[ 'phone2' ] ?? NULL ,
                            'can_delete' => FALSE ,
                            'status'     => Status::ACTIVE
                        ] );

                    $branch->update( [ 'code' => recordId( 'BR' , $branch , 3 ) ] );

                    $subscription = TenantSubscription::create( [
                        'phone'                => $data[ 'mobileMoneyNumber' ] ,
                        'branch_id'            => $branch->id ,
                        'amount'               => $data[ 'amountPaid' ] ,
                        'billing_cycle_id'     => $data[ 'billingCycleId' ] ,
                        'tenant_id'            => $data[ 'tenant' ] ,
                        'subscription_plan_id' => $data[ 'subscriptionPlanId' ] ,
                        'status'               => Status::INACTIVE ,
                    ] );

                    $transaction = PaymentTransaction::create( [
                        'amount'           => $data[ 'amountPaid' ] ,
                        'phone'            => $data[ 'mobileMoneyNumber' ] ,
                        'data'             => [
                            'email'         => $data[ 'adminEmail' ] ,
                            'business_name' => $data[ 'businessName' ]
                        ] ,
                        'payment_type'     => SystemPaymentType::SUBSCRIPTION ,
                        'payment_type_id'  => $subscription->id ,
                        'tenant_branch_id' => $branch->id ,
                        'tenant_id'        => $data[ 'tenant' ] ,
                    ] );

                    $cycle = BillingCycle::find( $data[ 'billingCycleId' ] );

                    $subscription->update( [
                        'invoice_no' => recordId( 'INV' , $subscription ) ,
                        'expires_at' => now()->addMonths( $cycle->multiplier )
                    ] );

                    InitiatePaymentJob::dispatch( $transaction );

                    return response()->json();
                } );

            } catch ( \Throwable $e ) {
                return response( [ 'status' => FALSE , 'message' => $e->getMessage() ] , 422 );
            }

        }

        public function destroy(Tenant $tenant)
        {
            $tenant->delete();
        }
    }
