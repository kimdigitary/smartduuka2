<?php

    namespace App\Jobs;

    use App\Models\PaymentTransaction;
    use App\Payments\PaymentsController;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;

    class InitiatePaymentJob implements ShouldQueue
    {
        use Queueable;

        public function __construct(public PaymentTransaction $payment_transaction) {}

        public function handle(PaymentsController $payments_controller) : void
        {
            $payments_controller->charge( $this->payment_transaction );
        }
    }
