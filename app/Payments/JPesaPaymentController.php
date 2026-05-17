<?php

    namespace App\Http\Controllers;

    use App\Helpers\AdamTelegram;
    use App\Jobs\SendExceptionJob;
    use App\Jobs\SendTelegramMessageJob;
    use App\Models\Deposit;
    use App\Models\Router;
    use App\Models\RouterPrice;
    use App\Models\UserVouchers;
    use App\Models\Voucher;
    use App\Payments\Gateways\JPesaGateway;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    // Added this line

    class JPesaPaymentController extends Controller
    {
        public function pay(array $data , AdamTelegram $adam_telegram , string $transaction_id , JPesaGateway $jpesaGateway) // Modified signature
        {
            $jpesaGateway->pay( $data , $transaction_id ); // Called the gateway's pay method
        }

        public function success(Request $request)
        {
            if ( $request->has( 'status' ) && $request->input( 'status' ) == 'approved' ) {
                $transaction_id      = $request->tx;
                $deposit             = Deposit::where( [ 'transaction_id' => $transaction_id ] )->first();
                $vendorTransactionId = $request->pp_id;
                if ( $deposit ) {
                    info( $deposit );
                    return DB::transaction( function () use ($deposit , $transaction_id , $request , $vendorTransactionId) {
                        $voucher  = Voucher::where( [ 'price' => $deposit->amount , 'used' => 0 , 'router_id' => $deposit->router->name ] )->first();
                        $duration = RouterPrice::where( [ 'router_id' => $deposit->router_id , 'amount' => $deposit->amount ] )->first()->profile;
                        UserVouchers::where( 'transaction_id' , $transaction_id )
                                    ->update( [
                                        'voucher'               => $voucher->code ,
                                        'duration'              => $duration ,
                                        'vendor_transaction_id' => $vendorTransactionId
                                    ] );
                        $voucher->update( [ 'used' => 1 ] );
                        $deposit->transaction_id = $vendorTransactionId;
                        if ( $request->has( 'totalTransactionCharge' ) ) {
                            $deposit->amount = ( (int) $deposit->amount ) - ( (int) $request->input( 'totalTransactionCharge' ) );
                        }
                        $deposit->status = 1;
                        $deposit->save();
                        try {
                            $router_id = $deposit->router->name;

                            SendTelegramMessageJob::dispatchAfterResponse(
                                deposit: $deposit ,
                                comment: "User $deposit->phone allocated $voucher->code for $router_id." ,
                                voucher: $voucher
                            );

                        } catch ( Exception $e ) {
                            info( $e->getMessage() );
                            SendTelegramMessageJob::dispatchAfterResponse(
                                deposit: $deposit ,
                                comment: "User could be allocated voucher for $router_id." ,
                                voucher: $voucher
                            );
                            SendExceptionJob::dispatchException( $e );
                        }
                        return response()->json();
                    } );
                }
            }
            return response()->json();
        }
    }
