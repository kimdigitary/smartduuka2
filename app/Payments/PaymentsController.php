<?php

    namespace App\Payments;

    use App\Enums\Status;
    use App\Enums\SubscriptionPaymentStatus;
    use App\Enums\SubscriptionPlanType;
    use App\Enums\SystemPaymentType;
    use App\Http\Controllers\Controller;
    use App\Jobs\SendEmailsJob;
    use App\Models\BranchModule;
    use App\Models\BusinessOnBoard;
    use App\Models\PaymentTransaction;
    use App\Models\SubscriptionPlan;
    use App\Models\Tenant;
    use App\Models\TenantBranch;
    use App\Models\TenantSubscription;
    use App\Payments\DTOs\PaymentRequest;
    use App\Payments\DTOs\WebhookPayload;
    use Database\Seeders\SystemModuleSeeder;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;

    class PaymentsController extends Controller
    {
        public function __construct(private readonly PaymentManager $payments) {}

        public function charge(PaymentTransaction $payment_transaction , ?string $gatewayName = NULL) : void
        {
            $gatewayName = $gatewayName ?? config( 'payments.default' , 'iotec' );
            $gateway     = $this->payments->gateway( $gatewayName );

            $transactionId = Str::uuid()->getHex();

            $paymentRequest = new PaymentRequest(
                phone: $payment_transaction->phone ,
                amount: isDev() ? 1000 : $payment_transaction->amount ,
                description: 'Smart Duuka Payments' ,
                transactionId: $transactionId ,
                notificationUrl: $this->webhookUrl( $gatewayName ) ,
                failureUrl: $this->webhookUrl( $gatewayName ) ,
            );

            $payment_transaction->update( [ 'transaction_id' => $transactionId ] );
            $gateway->charge( $paymentRequest );
        }

        public function webhook(Request $request , string $gateway) : JsonResponse
        {
//            try {
                $handler = $this->payments->gateway( $gateway );
                $payload = $handler->parseWebhook( $request );

                if ( $handler->isSuccessWebhook( $request ) ) {
                    return $this->handleSuccess( $payload );
                }
                elseif ( $handler->isFailureWebhook( $request ) ) {
                    return $this->handleFailure( $payload );
                }
//            } catch ( \Exception $e ) {
//                info( $e->getMessage() );
//                return response()->json();
//            }

            return response()->json();
        }

        private function handleSuccess(WebhookPayload $payload)
        {
//            try {
                return DB::transaction( function () use ($payload) {

                    $transaction = PaymentTransaction::where( 'transaction_id' , $payload->transactionId )->first();
                    if ( ! $transaction ) {
                        return response()->json();
                    }

                    if ( $transaction->payment_type == SystemPaymentType::SUBSCRIPTION ) {
                        $this->tenantSubscriptionSuccessful( $payload , $transaction );
                    }
                    if ( $transaction->payment_type == SystemPaymentType::BRANCH ) {
                        $this->newBranchPaymentSuccessful( $payload , $transaction );
                    }
                    if ( $transaction->payment_type == SystemPaymentType::MODULE ) {
                        $this->modulePaymentSuccessful( $transaction );
                    }

                    SendEmailsJob::dispatch(
                        $transaction->data[ 'email' ] ,
                        'Payment Successful - Smart Duuka' ,
                        'payments.paymentsuccess' ,
                        [
                            'username'       => $payload->raw[ 'payer_names' ] ?? '' ,
                            'business_name'  => $transaction->data[ 'business_name' ] ,
                            'amount_paid'    => number_format( $transaction->amount ) ,
                            'txn_id'         => $payload->gatewayRef ,
                            'payment_method' => 'Mobile Money' ,
                        ] ,
                    );
                    return response()->json();
                } );
//            } catch ( \Throwable $e ) {
//                info( $e->getMessage() );
//                return response()->json();
//            }
        }

        private function modulePaymentSuccessful(PaymentTransaction $payment_transaction) : void
        {
            $this->updateModules( $payment_transaction , enabled: TRUE );
        }

        private function failedModulePayment(PaymentTransaction $payment_transaction) : void
        {
            $this->updateModules( $payment_transaction , enabled: FALSE );
        }

        private function updateModules(PaymentTransaction $payment_transaction , bool $enabled) : void
        {
            $ids = json_decode( $payment_transaction->payment_type_id );

            foreach ( $ids as $id ) {
                tenantContext( function () use ($payment_transaction , $id , $enabled) {
                    BranchModule::where( [
                        'branch_id'        => $payment_transaction->tenant_branch_id ,
                        'system_module_id' => $id ,
                    ] )?->update( [ 'enabled' => $enabled ] );
                } );
            }
        }

        private function tenantSubscriptionSuccessful(WebhookPayload $payload , PaymentTransaction $payment_transaction) : void
        {
            $subscription = TenantSubscription::find( $payment_transaction->payment_type_id );

            $subscription?->update( [
                'payment_status' => SubscriptionPaymentStatus::Paid ,
                'status'         => Status::ACTIVE ,
                'transaction_id' => $payload->gatewayRef ,
                'payer_name'     => $payload->payerName ,
            ] );

            $modules = $payment_transaction->data[ 'modules' ] ?? [];

            if ( ! empty( $modules ) ) {
                foreach ( json_decode( $modules , TRUE ) as $module ) {
                    tenantContext( fn() => BranchModule::updateOrInsert(
                        [
                            'branch_id'        => $payment_transaction->tenant_branch_id ,
                            'system_module_id' => $module[ 'id' ] ,
                        ] ,
                        [
                            'enabled' => $module[ 'enabled' ] ,
                        ]
                    ) , $payment_transaction->tenant_id
                    );
                }
            }

            TenantSubscription::where( 'tenant_id' , $subscription->tenant_id )
                              ->where( 'id' , '!=' , $subscription->id )
                              ->where( 'status' , Status::ACTIVE )
                              ->update( [ 'status' => Status::INACTIVE ] );

            $plan = SubscriptionPlan::find( $subscription->subscription_plan_id );

            if ( $plan?->type === SubscriptionPlanType::Starter ) {
                $this->setupOnboarding( $subscription , $payload );
            }
            else {
                $subscription->branch->update( [ 'status' => Status::ACTIVE ] );
            }
        }

        private function newBranchPaymentSuccessful(WebhookPayload $payload , PaymentTransaction $payment_transaction) : void
        {
            $subscription = TenantSubscription::find( $payment_transaction->payment_type_id );

            $subscription?->update( [
                'payment_status' => SubscriptionPaymentStatus::Paid ,
                'status'         => Status::ACTIVE ,
                'transaction_id' => $payload->gatewayRef ,
                'payer_name'     => $payload->payerName ,
            ] );

            $subscription->branch->update( [ 'status' => Status::ACTIVE ] );

            $plan = SubscriptionPlan::find( $subscription->subscription_plan_id );

            if ( $plan?->type === SubscriptionPlanType::Starter ) {
                $this->setupOnboarding( $subscription , $payload );
            }
            else {
                tenantContext( function () use ($payment_transaction) {
                    $seeder = new SystemModuleSeeder();
                    $seeder->run( $payment_transaction->tenant_branch_id );
                } , $payment_transaction->tenant_id );
            }
        }

        private function failedSubscriptionPayment(WebhookPayload $payload , PaymentTransaction $payment_transaction) : void
        {
            try {
                DB::transaction( function () use ($payload , $payment_transaction) {
                    $subscription = TenantSubscription::find( $payment_transaction->payment_type_id );

                    $subscription?->update( [ 'payment_status' => SubscriptionPaymentStatus::Failed ] );

                    $tenant_url = Tenant::find( $subscription->tenant_id )?->frontend_url;
                    $plan       = SubscriptionPlan::find( $subscription->subscription_plan_id );

                    $starter = $plan?->type === SubscriptionPlanType::Starter;


                    $retryLink = $starter ? 'https://smartduuka.com/pricing' : "$tenant_url/subscriptions";

                    if ( $starter ) {
                        $onboard = BusinessOnBoard::where( 'tenant' , $subscription->tenant_id )->latest()->first();
                        Artisan::call( 'delete-tenant' , [ 'id' => $subscription->tenant_id ] );
                        BusinessOnBoard::where( 'tenant' , $subscription->tenant_id )?->delete();
                        TenantBranch::where( 'tenant_id' , $subscription->tenant_id )?->delete();
                        SendEmailsJob::dispatch(
                            $onboard->admin_email ,
                            'Payment Failed - Smart Duuka' ,
                            'tenants.paymentfailed' ,
                            [
                                'username'           => $payload->raw[ 'payer_names' ] ?? '' ,
                                'business_name'      => $onboard->name ,
                                'dashboard_link'     => $onboard->domain ,
                                'amount_paid'        => number_format( $subscription->amount ) ,
                                'retry_payment_link' => $retryLink ,
                            ] ,
                        );
                    }
                    return response()->json();
                } );
                return;
            } catch ( \Throwable $e ) {
                info( $e->getMessage() );
                response()->json();
                return;
            }
        }

        private function handleFailure(WebhookPayload $payload , PaymentTransaction $payment_transaction) : JsonResponse
        {
            $transaction = PaymentTransaction::where( 'transaction_id' , $payload->transactionId )->first();
            if ( ! $transaction ) {
                return response()->json();
            }
            if ( $transaction->payment_type == SystemPaymentType::SUBSCRIPTION ) {
                $this->failedSubscriptionPayment( $transaction->payment_type_id , $payload );
            }
            if ( $transaction->payment_type == SystemPaymentType::MODULE ) {
                $this->failedModulePayment( $transaction->payment_type_id );
            }
            SendEmailsJob::dispatch(
                $transaction->data[ 'email' ] ,
                'Payment Failed - Smart Duuka' ,
                'payments.paymentfailed' ,
                [
                    'username'    => $payload->raw[ 'payer_names' ] ?? '' ,
                    'amount_paid' => number_format( $transaction->amount ) ,
                ] ,
            );
            return response()->json();
        }

        private function webhookUrl(string $gateway) : string
        {
            if ( app()->isLocal() ) {
                return rtrim( config( 'payments.local_tunnel_url' , '' ) , '/' ) . "/api/webhook/{$gateway}";
            }

            return route( 'webhook.gateway' , [ 'gateway' => $gateway ] );
        }

        /**
         * @param \LaravelIdea\Helper\App\Models\_IH_TenantSubscription_C|array|TenantSubscription|null $subscription
         * @param WebhookPayload                                                                        $payload
         *
         * @return void
         */
        private function setupOnboarding(TenantSubscription | null $subscription , WebhookPayload $payload) : void
        {
            $onboard = BusinessOnBoard::where( 'tenant' , $subscription?->tenant_id )->latest()->first();
            $onboard->update( [ 'status' => Status::ACTIVE ] );
            Artisan::call( 'create-tenant' , [ 'id' => $subscription->tenant_id , 'branch_id' => $subscription->branch_id ] );
            SendEmailsJob::dispatch(
                $onboard->admin_email ,
                'Payment Successful - Smart Duuka' ,
                'tenants.paymentsuccess' ,
                [
                    'username'        => $payload->raw[ 'payer_names' ] ?? '' ,
                    'business_name'   => $onboard->name ,
                    'dashboard_link'  => $onboard->domain ,
                    'amount_paid'     => number_format( $subscription->amount ) ,
                    'txn_id'          => $payload->gatewayRef ,
                    'new_expiry_date' => $subscription->expires_at ,
                    'payment_method'  => 'Mobile Money' ,
                ] ,
            );
        }
    }