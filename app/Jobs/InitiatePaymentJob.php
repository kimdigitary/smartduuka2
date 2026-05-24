<?php

    namespace App\Jobs;

    use App\Models\PaymentTransaction;
    use App\Payments\PaymentsController;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;

    class InitiatePaymentJob implements ShouldQueue
    {
        use Queueable;

        public function __construct(public PaymentTransaction $payment_transaction , public array $gateways = [])
        {
            $this->gateways = empty( $gateways ) ? [ 'yo_uganda' , 'jpesa' , 'iotec' ] : $gateways;
        }

        public function handle(PaymentsController $payments_controller) : void
        {
            if ( empty( $this->gateways ) ) {
                info( "All payment gateways failed for transaction: {$this->payment_transaction->id}" );
                return;
            }

            info( "Initiating payment for transaction: {$this->payment_transaction->id}" );
            $payments_controller->charge( $this->payment_transaction );
        }
    }
