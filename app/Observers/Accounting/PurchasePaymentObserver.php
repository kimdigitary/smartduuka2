<?php

    namespace App\Observers\Accounting;

    use App\Models\PurchasePayment;
    use App\Services\Accounting\OperationalPostingService;
    use Illuminate\Support\Facades\Log;

    class PurchasePaymentObserver
    {
        public function __construct(private readonly OperationalPostingService $posting)
        {
        }

        public function created(PurchasePayment $payment) : void
        {
            try {
                $this->posting->postPurchasePayment( $payment );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting auto-post (purchase payment) failed: ' . $e->getMessage(), [ 'purchase_payment_id' => $payment->id ] );
            }
        }

        public function deleted(PurchasePayment $payment) : void
        {
            try {
                $this->posting->reverse( [ 'purchase_payment' ], $payment->id );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting reversal (purchase payment) failed: ' . $e->getMessage(), [ 'purchase_payment_id' => $payment->id ] );
            }
        }
    }
