<?php

    namespace App\Providers;

    use App\Models\CommissionPayout;
    use App\Models\CustomerPayment;
    use App\Models\CustomerWalletTransaction;
    use App\Models\Damage;
    use App\Models\Expense;
    use App\Models\ExpensePayment;
    use App\Models\Order;
    use App\Models\ProductionProcess;
    use App\Models\Purchase;
    use App\Models\PurchasePayment;
    use App\Observers\Accounting\CommissionPayoutObserver;
    use App\Observers\Accounting\CustomerPaymentObserver;
    use App\Observers\Accounting\CustomerWalletTransactionObserver;
    use App\Observers\Accounting\DamageObserver;
    use App\Observers\Accounting\ExpenseObserver;
    use App\Observers\Accounting\ExpensePaymentObserver;
    use App\Observers\Accounting\OrderObserver;
    use App\Observers\Accounting\ProductionProcessObserver;
    use App\Observers\Accounting\PurchaseObserver;
    use App\Observers\Accounting\PurchasePaymentObserver;
    use Illuminate\Support\ServiceProvider;

    /**
     * Wires operational models to the IFRS ledger via observers (Phase 7).
     * Non-invasive: the operational flows are untouched. Entries post on `created`
     * and reverse on `deleted` (void), so the ledger tracks the lifecycle.
     */
    class AccountingObserverServiceProvider extends ServiceProvider
    {
        public function boot() : void
        {
            Order::observe( OrderObserver::class );
            Expense::observe( ExpenseObserver::class );
            ExpensePayment::observe( ExpensePaymentObserver::class );
            CustomerWalletTransaction::observe( CustomerWalletTransactionObserver::class );
            Purchase::observe( PurchaseObserver::class );
            PurchasePayment::observe( PurchasePaymentObserver::class );
            CustomerPayment::observe( CustomerPaymentObserver::class );
            Damage::observe( DamageObserver::class );
            CommissionPayout::observe( CommissionPayoutObserver::class );
            ProductionProcess::observe( ProductionProcessObserver::class );
        }
    }
