<?php

    namespace App\Observers\Accounting;

    use App\Models\Expense;
    use App\Services\Accounting\OperationalPostingService;
    use Illuminate\Support\Facades\Log;

    class ExpenseObserver
    {
        public function __construct(private readonly OperationalPostingService $posting)
        {
        }

        public function created(Expense $expense) : void
        {
            try {
                $this->posting->postExpense( $expense );
            } catch ( \Throwable $e ) {
                Log::error( 'Accounting auto-post (expense) failed: ' . $e->getMessage(), [ 'expense_id' => $expense->id ] );
            }
        }
    }
