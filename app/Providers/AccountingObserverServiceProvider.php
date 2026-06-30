<?php

    namespace App\Providers;

    use App\Models\CustomerPayment;
    use App\Models\CustomerWalletTransaction;
    use App\Models\Expense;
    use App\Models\Order;
    use App\Models\Purchase;
    use App\Models\PurchasePayment;
    use App\Observers\Accounting\CustomerPaymentObserver;
    use App\Observers\Accounting\CustomerWalletTransactionObserver;
    use App\Observers\Accounting\ExpenseObserver;
    use App\Observers\Accounting\OrderObserver;
    use App\Observers\Accounting\PurchaseObserver;
    use App\Observers\Accounting\PurchasePaymentObserver;
    use Illuminate\Support\ServiceProvider;

    /**
     * Wires operational models to the IFRS ledger via observers (Phase 7).
     * Non-invasive: the operational flows are untouched; entries post on `created`.
     */
    class AccountingObserverServiceProvider extends ServiceProvider
    {
        public function boot() : void
        {
            Order::observe( OrderObserver::class );
            Expense::observe( ExpenseObserver::class );
            CustomerWalletTransaction::observe( CustomerWalletTransactionObserver::class );
            Purchase::observe( PurchaseObserver::class );
            PurchasePayment::observe( PurchasePaymentObserver::class );
            CustomerPayment::observe( CustomerPaymentObserver::class );
        }
    }
