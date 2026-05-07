<?php

    namespace App\Http\Controllers;

    use App\Enums\SubscriptionPaymentStatus;
    use App\Models\TenantSubscription;
    use App\YoPayments\YoAPI;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Str;
    use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;

    class PaymentsController extends Controller
    {
        public function yoUganda(Request $request)
        {
            $username          = config( 'payments.yo.username' );
            $password          = config( 'payments.yo.password' );
            $mode              = app()->isLocal() ? 'sandbox' : 'production';
            $yoAPI             = new YoAPI( username: $username , password: $password , mode: $mode );
            $is_from_yo_uganda = $yoAPI->receive_payment_notification( $request );

            try {
                if ( $is_from_yo_uganda ) {
                    $subscription = TenantSubscription::where( [ 'transaction_id' => $request->external_ref ] )->first();
                    if ( $subscription ) {
                        $subscription->update( [
                            'payment_status' => SubscriptionPaymentStatus::Paid ,
                        ] );
                        tenancy()->initialize( $subscription->tenant_id );
                        Cache::forget( "tenant_subscription_{$subscription->tenant_id}" );
                        tenancy()->end();
                    }
                }
                return response()->json();
            } catch ( TenantCouldNotBeIdentifiedById $e ) {
                info( $e->getMessage() );
                return response()->json();
            }
        }

        public function yoPay(TenantSubscription $tenantSubscription)
        {
            $username = config( 'payments.yo.username' );
            $password = config( 'payments.yo.password' );

            $transaction_id = Str::uuid()->getHex();
            $phone          = $tenantSubscription->phone;
            $phone          = '256' . substr( $phone , 1 );

            $mode   = app()->isLocal() ? 'sandbox' : 'production';
            $yoAPI  = new YoAPI( username: $username , password: $password , mode: $mode );
            $amount = $tenantSubscription->amount;

            $title = 'Smart Duuka Subscription';

            $yoAPI->set_external_reference( $transaction_id );
            $yoAPI->set_nonblocking( 'TRUE' );

            if ( app()->isLocal() ) {
                $ipn = 'https://jersey-oral-clicking-spotlight.trycloudflare.com/api/webhook/yo';
            }

            else {
                $ipn = route( 'webhook.yo' );
            }

            $yoAPI->set_instant_notification_url( $ipn );
            $yoAPI->set_failure_notification_url( route( 'webhook.yo' ) );
            $response = $yoAPI->ac_deposit_funds( $phone , $amount , $title );

            info( $response );
            if ( $response[ 'Status' ] == 'OK' ) {
                $tenantSubscription->update( [ 'transaction_id' => $transaction_id ] );
            }
        }
    }
