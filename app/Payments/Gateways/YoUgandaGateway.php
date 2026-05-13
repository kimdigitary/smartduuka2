<?php

    namespace App\Payments\Gateways;

    use App\Payments\Contracts\PaymentGateway;
    use App\Payments\DTOs\PaymentRequest;
    use App\Payments\DTOs\PaymentResult;
    use App\Payments\DTOs\WebhookPayload;
    use App\YoPayments\YoAPI;
    use Illuminate\Http\Request;

    class YoUgandaGateway implements PaymentGateway
    {
        public function charge(PaymentRequest $payment) : PaymentResult
        {
            $yoAPI = $this->makeClient();

            $yoAPI->set_external_reference( $payment->transactionId );
            $yoAPI->set_nonblocking( 'TRUE' );
            $yoAPI->set_instant_notification_url( $payment->notificationUrl );
//            $yoAPI->set_failure_notification_url( $payment->failureUrl ?: $payment->notificationUrl );
            $yoAPI->set_failure_notification_url( $payment->notificationUrl );

            $response = $yoAPI->ac_deposit_funds(
                $this->normalisePhone( $payment->phone ) ,
                $payment->amount ,
                $payment->description ,
            );

            if ( ( $response[ 'Status' ] ?? '' ) === 'OK' ) {
                return PaymentResult::pending(
                    transactionId: $payment->transactionId ,
                    message: 'Payment request sent' ,
                    raw: $response ,
                );
            }

            return PaymentResult::failed(
                message: $response[ 'StatusMessage' ] ?? 'Yo! Uganda collection failed' ,
                raw: $response ,
            );
        }

        public function isSuccessWebhook(Request $request) : bool
        {
            return new YoAPI(
                username: config( 'payments.yo.username' ) ,
                password: config( 'payments.yo.password' ) ,
                mode: $this->mode() ,
            )->receive_payment_notification( $request );
        }

        public function isFailureWebhook(Request $request) : bool
        {
            return new YoAPI(
                username: config( 'payments.yo.username' ) ,
                password: config( 'payments.yo.password' ) ,
                mode: $this->mode() ,
            )->receive_payment_failure_notification( $request );
        }

        public function parseWebhook(Request $request) : WebhookPayload
        {
            $isSuccess = $this->isSuccessWebhook( $request );

            return new WebhookPayload(
                transactionId: $isSuccess
                    ? ( $request->external_ref ?? '' )
                    : ( $request->failed_transaction_reference ?? '' ) ,
                status: $isSuccess ? 'success' : 'failed' ,
                gatewayRef: $request->network_ref ?? '' ,
                message: $request->StatusMessage ?? '' ,
                payerName: $request->payer_names ?? '' ,
                raw: $request->all() ,
            );
        }

        private function makeClient() : YoAPI
        {
            return new YoAPI(
                username: config( 'payments.yo.username' ) ,
                password: config( 'payments.yo.password' ) ,
                mode: $this->mode() ,
            );
        }

        private function mode() : string
        {
            return app()->isLocal() ? 'sandbox' : 'production';
        }

        private function normalisePhone(string $phone) : string
        {
            if ( str_starts_with( $phone , '+256' ) ) {
                return substr( $phone , 1 );
            }

            if ( str_starts_with( $phone , '0' ) ) {
                return '256' . substr( $phone , 1 );
            }

            return $phone;
        }
    }
