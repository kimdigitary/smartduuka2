<?php

    namespace App\Payments\Gateways;

    use App\Jobs\SendExceptionJob;
    use App\Payments\Contracts\PaymentGateway;
    use App\Payments\DTOs\PaymentRequest;
    use App\Payments\DTOs\PaymentResult;
    use App\Payments\DTOs\WebhookPayload;
    use Exception;
    use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Http;

    class JPesaGateway implements PaymentGateway
    {

        public function charge(PaymentRequest $payment) : PaymentResult
        {
            try {
                $amount          = $payment->amount;
                $phone           = normalisePhone( $payment->phone );
                $transaction_id  = $payment->transactionId;
                $notificationUrl = $payment->notificationUrl;
                $call_back_url   = $notificationUrl;

                $DATA = $this->makeXmlData(
                    phone: $phone ,
                    amount: $amount ,
                    callback: $call_back_url ,
                    transactionId: $transaction_id ,
                    description: $payment->description
                );

                $response = $this->getXMLResponse( $DATA );

                if ( isset( $response[ 'api_status' ] ) && strtolower( $response[ 'api_status' ] ) == 'success' ) {
                    return PaymentResult::pending(
                        transactionId: $transaction_id ,
                        message: $response[ 'msg' ] ?? 'Payment request sent' ,
                        raw: $response ,
                    );
                }

                return PaymentResult::failed(
                    message: $response[ 'msg' ] ?? 'JPesa collection failed' ,
                    raw: $response ,
                );
            } catch ( Exception $e ) {
                info( $e->getMessage() );
                SendExceptionJob::dispatchException( $e );
                return PaymentResult::failed(
                    message: 'An error occurred during JPesa payment initiation: ' . $e->getMessage() ,
                    raw: [ 'exception' => $e->getMessage() ] ,
                );
            }
        }

        public function isSuccessWebhook(Request $request) : bool
        {
            return $request->has( 'status' ) && $request->input( 'status' ) == 'approved';
        }

        public function isFailureWebhook(Request $request) : bool
        {
            return $request->has( 'status' ) && $request->input( 'status' ) != 'approved';
        }

        public function parseWebhook(Request $request) : WebhookPayload
        {
            $isSuccess = $this->isSuccessWebhook( $request );

            return new WebhookPayload(
                transactionId: $request->input( 'tx' ) ?? '' ,
                status: $isSuccess ? 'success' : 'failed' ,
                gatewayRef: $request->input( 'pp_id' ) ?? '' ,
                message: $isSuccess ? 'Payment Approved' : ( $request->input( 'msg' ) ?? 'Payment Failed' ) ,
                payerName: 'No name' ,
                raw: $request->all() ,
            );
        }

        private function makeXmlData(string $phone , float $amount , string $callback , string $transactionId , string $description) : string
        {
            $api_key = config( 'payments.jpesa.api_key'  ); // Use config for API key
            return "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
                     <g7bill>
                       <_key_>$api_key</_key_>
                       <cmd>account</cmd>
                       <action>credit</action>
                       <pt>mm</pt>
                       <mobile>$phone</mobile>
                       <amount>$amount</amount>
                       <callback>$callback</callback>
                       <tx>$transactionId</tx>
                       <description>$description</description>
                     </g7bill>";
        }

        private function getXMLResponse(string $xml) : array
        {
            try {
                $response = Http::withoutVerifying()
                                ->connectTimeout( 120 )
                                ->timeout( 400 )
                                ->withHeaders( [
                                    'Content-transfer-encoding' => 'text' ,
                                ] )
                                ->withBody( $xml , 'text/xml' )
                                ->post( 'https://my.jpesa.com/api/' );

                if ( $response->failed() ) {
                    return [
                        'api_status' => 'failed' ,
                        'msg'        => 'HTTP request failed with status: ' . $response->status()
                    ];
                }

                $xml_response = $response->body();

                // Attempt to parse the XML response
                $xmlObject = simplexml_load_string( $xml_response );

                if ( $xmlObject === FALSE ) {
                    return [
                        'api_status'   => 'failed' ,
                        'msg'          => 'Failed to parse XML response.' ,
                        'raw_response' => $xml_response
                    ];
                }

                $json = json_encode( $xmlObject );
                return json_decode( $json , TRUE );

            } catch ( ConnectionException $e ) {
                return [
                    'api_status' => 'failed' ,
                    'msg'        => 'Connection error: ' . $e->getMessage()
                ];
            } catch ( Exception $e ) {
                return [
                    'api_status'   => 'failed' ,
                    'msg'          => 'Failed to process XML response: ' . $e->getMessage() ,
                    'raw_response' => $xml_response ?? NULL
                ];
            }
        }
    }