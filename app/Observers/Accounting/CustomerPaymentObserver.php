<?php

    namespace App\Observers\Accounting;

    use App\Models\CustomerPayment;
    use App\Services\Accounting\OperationalPostingService;
    use Illuminate\Support\Facades\Log;

    class CustomerPaymentObserver
    {
        public function __construct(private readonly OperationalPostingService $posting)
        {
        }

        public function created(CustomerPayment $payment) : void
        {
            try {
                $this->posting->postCustomerPayment( $payment );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting auto-post (customer payment) failed: ' . $e->getMessage(), [ 'customer_payment_id' => $payment->id ] );
            }
        }

        public function deleted(CustomerPayment $payment) : void
        {
            try {
                $this->posting->reverse( [ 'customer_payment' ], $payment->id );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting reversal (customer payment) failed: ' . $e->getMessage(), [ 'customer_payment_id' => $payment->id ] );
            }
        }
    }
