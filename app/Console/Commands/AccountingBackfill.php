<?php

    namespace App\Console\Commands;

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
    use App\Services\Accounting\AccountingContext;
    use App\Services\Accounting\OperationalPostingService;
    use Illuminate\Console\Command;

    /**
     * Post historical operational records to the IFRS ledger. Idempotent — records
     * already posted (matched by source + source_id) are skipped. Run per tenant.
     *
     *   php artisan accounting:backfill            (all sources)
     *   php artisan accounting:backfill --source=sale
     */
    class AccountingBackfill extends Command
    {
        protected $signature   = 'accounting:backfill {--source=all : all|sale|expense|expense-payment|wallet|purchase|purchase-payment|customer-payment|damage|commission-payout|production}';
        protected $description = 'Backfill the IFRS ledger from historical operational records (idempotent).';

        public function handle(OperationalPostingService $posting) : int
        {
            if ( ! AccountingContext::ensure() ) {
                $this->error( 'No accounting entity for this tenant. Run accounting:bootstrap first.' );

                return self::FAILURE;
            }

            $source = (string) $this->option( 'source' );

            if ( in_array( $source, [ 'all', 'sale' ], TRUE ) ) {
                $this->info( 'Backfilling sales…' );
                Order::query()->withoutGlobalScopes()->chunkById( 200, function ($orders) use ( $posting ) {
                    foreach ( $orders as $order ) {
                        $this->safe( fn () => $posting->postSale( $order ) );
                    }
                } );
            }

            if ( in_array( $source, [ 'all', 'expense' ], TRUE ) ) {
                $this->info( 'Backfilling expenses…' );
                Expense::query()->withoutGlobalScopes()->chunkById( 200, function ($expenses) use ( $posting ) {
                    foreach ( $expenses as $expense ) {
                        $this->safe( fn () => $posting->postExpense( $expense ) );
                    }
                } );
            }

            if ( in_array( $source, [ 'all', 'expense-payment' ], TRUE ) ) {
                $this->info( 'Backfilling expense payments…' );
                ExpensePayment::query()->withoutGlobalScopes()->chunkById( 200, function ($payments) use ( $posting ) {
                    foreach ( $payments as $payment ) {
                        $this->safe( fn () => $posting->postExpensePayment( $payment ) );
                    }
                } );
            }

            if ( in_array( $source, [ 'all', 'wallet' ], TRUE ) ) {
                $this->info( 'Backfilling wallet transactions…' );
                CustomerWalletTransaction::query()->withoutGlobalScopes()->chunkById( 200, function ($txns) use ( $posting ) {
                    foreach ( $txns as $txn ) {
                        $this->safe( fn () => $posting->postWallet( $txn ) );
                    }
                } );
            }

            if ( in_array( $source, [ 'all', 'purchase' ], TRUE ) ) {
                $this->info( 'Backfilling purchases…' );
                Purchase::query()->withoutGlobalScopes()->chunkById( 200, function ($purchases) use ( $posting ) {
                    foreach ( $purchases as $purchase ) {
                        $this->safe( fn () => $posting->postPurchase( $purchase ) );
                    }
                } );
            }

            if ( in_array( $source, [ 'all', 'purchase-payment' ], TRUE ) ) {
                $this->info( 'Backfilling supplier payments…' );
                PurchasePayment::query()->withoutGlobalScopes()->chunkById( 200, function ($payments) use ( $posting ) {
                    foreach ( $payments as $payment ) {
                        $this->safe( fn () => $posting->postPurchasePayment( $payment ) );
                    }
                } );
            }

            if ( in_array( $source, [ 'all', 'customer-payment' ], TRUE ) ) {
                $this->info( 'Backfilling customer payments…' );
                CustomerPayment::query()->withoutGlobalScopes()->chunkById( 200, function ($payments) use ( $posting ) {
                    foreach ( $payments as $payment ) {
                        $this->safe( fn () => $posting->postCustomerPayment( $payment ) );
                    }
                } );
            }

            if ( in_array( $source, [ 'all', 'damage' ], TRUE ) ) {
                $this->info( 'Backfilling inventory write-offs…' );
                Damage::query()->withoutGlobalScopes()->chunkById( 200, function ($damages) use ( $posting ) {
                    foreach ( $damages as $damage ) {
                        $this->safe( fn () => $posting->postDamage( $damage ) );
                    }
                } );
            }

            if ( in_array( $source, [ 'all', 'commission-payout' ], TRUE ) ) {
                $this->info( 'Backfilling commission payouts…' );
                CommissionPayout::query()->withoutGlobalScopes()->chunkById( 200, function ($payouts) use ( $posting ) {
                    foreach ( $payouts as $payout ) {
                        $this->safe( fn () => $posting->postCommissionPayout( $payout ) );
                    }
                } );
            }

            if ( in_array( $source, [ 'all', 'production' ], TRUE ) ) {
                $this->info( 'Backfilling production completions…' );
                ProductionProcess::query()->withoutGlobalScopes()->chunkById( 200, function ($processes) use ( $posting ) {
                    foreach ( $processes as $process ) {
                        $this->safe( fn () => $posting->postProduction( $process ) );
                    }
                } );
            }

            $this->info( 'Backfill complete.' );

            return self::SUCCESS;
        }

        private function safe(callable $fn) : void
        {
            try {
                $fn();
            } catch ( \Throwable $e ) {
                $this->warn( '  skipped: ' . $e->getMessage() );
            }
        }
    }
