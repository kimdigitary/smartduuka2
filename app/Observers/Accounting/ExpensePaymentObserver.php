<?php

    namespace App\Observers\Accounting;

    use App\Models\ExpensePayment;
    use App\Services\Accounting\OperationalPostingService;
    use Illuminate\Support\Facades\Log;

    class ExpensePaymentObserver
    {
        public function __construct(private readonly OperationalPostingService $posting)
        {
        }

        public function created(ExpensePayment $payment) : void
        {
            try {
                $this->posting->postExpensePayment( $payment );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting auto-post (expense payment) failed: ' . $e->getMessage(), [ 'expense_payment_id' => $payment->id ] );
            }
        }

        public function deleted(ExpensePayment $payment) : void
        {
            try {
                $this->posting->reverse( [ 'expense_payment' ], $payment->id );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting reversal (expense payment) failed: ' . $e->getMessage(), [ 'expense_payment_id' => $payment->id ] );
            }
        }
    }
