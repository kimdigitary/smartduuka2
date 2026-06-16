<?php

    namespace App\Payments\Gateways;

    use App\Payments\Contracts\PaymentGateway;
    use App\Payments\DTOs\PaymentRequest;
    use App\Payments\DTOs\PaymentResult;
    use App\Payments\DTOs\WebhookPayload;
    use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Http;

    class IotecGateway implements PaymentGateway
    {
        private const string COLLECT_URL = 'https://pay.iotec.io/api/collections/collect';
        private const string TOKEN_URL   = 'https://id.iotec.io/connect/token';

        /**
         * @throws ConnectionException
         */
        public function charge(PaymentRequest $payment) : PaymentResult
        {
            $response = Http::withToken( $this->getAccessToken() )
                            ->post( self::COLLECT_URL , [
                                'externalId' => $payment->transactionId ,
                                'payer'      => $payment->phone ,
                                'amount'     => $payment->amount ,
                                'payeeNote'  => $payment->description ,
                                'walletId'   => config( 'payments.iotec.iotec_wallet_id' ) ,
                            ] );


            $body = $response->json();

            if ( isset( $body[ 'status' ] ) && strtolower( $body[ 'status' ] ) === 'pending' ) {
                return PaymentResult::pending(
                    transactionId: $payment->transactionId ,
                    message: $body[ 'statusMessage' ] ?? '' ,
                    raw: $body ,
                );
            }

            return PaymentResult::failed(
                message: $body[ 'statusMessage' ] ?? 'Iotec collection failed' ,
                raw: $body ,
            );
        }

        public function isSuccessWebhook(Request $request) : bool
        {
            return $request->has( 'status' )
                && strtolower( $request->input( 'status' ) ) === 'success';
        }

        public function isFailureWebhook(Request $request) : bool
        {
            return $request->has( 'status' )
                && strtolower( $request->input( 'status' ) ) === 'failed';
        }

        public function parseWebhook(Request $request) : WebhookPayload
        {
            return new WebhookPayload(
                transactionId: $request->input( 'externalId' , '' ) ,
                status: strtolower( $request->input( 'status' , 'failed' ) ) ,
                gatewayRef: $request->input( 'vendorTransactionId' , '' ) ,
                message: $request->input( 'statusMessage' , '' ) ,
                payerName: $request->input( 'payerName' , '' ) ,
                raw: $request->all() ,
            );
        }


        /**
         * @throws ConnectionException
         */
        private function getAccessToken() : ?string
        {
            try {
                $response = Http::asForm()->post( self::TOKEN_URL , [
                    'client_id'     => config( 'payments.iotec.iotec_client_id' ) ,
                    'client_secret' => config( 'system.iotec_secrete' ) ,
                    'grant_type'    => 'client_credentials' ,
                ] );
                return $response[ 'access_token' ];
            } catch ( ConnectionException $e ) {
                info( $e->getMessage() );
                return NULL;
            }
        }
    }
