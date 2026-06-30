<?php

    namespace App\Services\Accounting;

    use App\Enums\CustomerPaymentType;
    use App\Enums\CustomerWalletTransactionType;
    use App\Enums\DamageStatus;
    use App\Enums\OrderType;
    use App\Enums\ProductionProcessStatus;
    use App\Enums\PurchaseType;
    use App\Enums\QuotationStatus;
    use App\Exceptions\Accounting\ClosedPeriodException;
    use App\Models\Accounting\PostingAlert;
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
    use IFRS\Models\Transaction;
    use Illuminate\Support\Carbon;
    use Smartisan\Settings\Facades\Settings;

    /**
     * Maps operational records (sales, expenses, wallet movements) to IFRS ledger
     * entries via PostingService — the backend equivalent of the frontend
     * `lib/ifrs/events.ts` + `posting.ts buildTransactions`. Non-invasive: called
     * from model observers, never from the operational flows themselves. Idempotent
     * via (source, source_id).
     *
     * Accounts come from the tenant's default-accounts map (Settings 'accounting').
     */
    class OperationalPostingService
    {
        private const EPS = 0.005;

        /** Cached payment-method → ledger-account map (Settings 'accounting'). */
        private ?array $methodMap = NULL;

        public function __construct(private readonly PostingService $posting)
        {
        }

        /**
         * Post (or restate) a sale to match the order's CURRENT state. Reconciles the
         * revenue and COGS entries against whatever is already on the ledger for this
         * order, so the same path handles a fresh sale, an EDIT (qty/price/payment
         * change), a QUOTATION→SALE conversion, and a void — always posting only the
         * delta. A return order posts the mirror image (credit note + stock restored).
         * Idempotent: a no-change re-run produces a zero delta and posts nothing.
         */
        public function postSale(Order $order) : void
        {
            $d          = $this->defaults();
            $revenue    = $this->acc( $d, 'salesRevenue' );
            $bank       = $this->acc( $d, 'bank' );
            $receivable = $this->acc( $d, 'receivable' );
            if ( ! $revenue || ! $bank || ! $receivable ) {
                return; // chart of accounts not configured
            }

            $ref  = $order->order_serial_no ?? '#' . $order->id;
            $date = $this->date( $order->created_at );

            $this->reconcile( 'sale', $order->id, $this->saleTargetLegs( $order, $d ),
                $date, ( ! empty( $order->original_order_id ) ? 'Sales return ' : 'Sale ' ) . $ref,
                $order->branch_id, $this->saleType( $order ) );

            $this->reconcile( 'sale_cogs', $order->id, $this->cogsTargetLegs( $order, $d ),
                $date, 'Cost of sale ' . $ref, $order->branch_id, 'JN' );
        }

        /** Remove a sale from the ledger (order deleted/voided) by reconciling to nil. */
        public function voidSale(Order $order) : void
        {
            $date = $this->date( $order->created_at ?? now() );
            $this->reconcile( 'sale', $order->id, [], $date, 'Sale void #' . $order->id, $order->branch_id, 'JN' );
            $this->reconcile( 'sale_cogs', $order->id, [], $date, 'Sale void #' . $order->id, $order->branch_id, 'JN' );
        }

        /** Whether the order is a financial sale right now (a quotation only is once converted). */
        private function isPostableSale(Order $order) : bool
        {
            $orderType = $order->order_type instanceof OrderType ? $order->order_type->value : (int) $order->order_type;
            if ( $orderType === OrderType::QUOTATION->value ) {
                return $order->quotation_status === QuotationStatus::CONVERTED;
            }

            return TRUE;
        }

        /** Target revenue/VAT/settlement legs for the order's current state (empty if not yet a sale). */
        private function saleTargetLegs(Order $order, array $d) : array
        {
            if ( ! $this->isPostableSale( $order ) ) {
                return [];
            }

            $salesRevenue   = $this->acc( $d, 'salesRevenue' );
            $serviceRevenue = $this->acc( $d, 'serviceRevenue' );
            $receivable     = $this->acc( $d, 'receivable' );
            $vatOutput      = $this->acc( $d, 'vatOutput' );
            $settlement     = $this->methodAccount( $d, $order->pos_payment_method ?? $order->payment_method );

            $net     = (float) $order->subtotal - (float) $order->discount;
            $tax     = (float) $order->tax;
            $paid    = (float) $order->paid;
            $balance = ( $net + $tax ) - $paid; // computed so the entry always balances

            $dr = empty( $order->original_order_id ); // normal sale debits cash/receivable; a return flips

            $legs = [];
            if ( $paid > self::EPS ) {
                $legs[] = $this->leg( $settlement, ! $dr, $paid );
            }
            if ( $balance > self::EPS ) {
                $legs[] = $this->leg( $receivable, ! $dr, $balance );
            }

            // Recognise revenue, splitting the product vs service portion into their
            // own accounts when a service-revenue account is configured.
            if ( $net > self::EPS ) {
                [ $productNet, $serviceNet ] = $this->splitRevenue( $order, $net, $serviceRevenue !== NULL );
                if ( $productNet > self::EPS ) {
                    $legs[] = $this->leg( $salesRevenue, $dr, $productNet );
                }
                if ( $serviceNet > self::EPS && $serviceRevenue ) {
                    $legs[] = $this->leg( $serviceRevenue, $dr, $serviceNet );
                }
            }

            if ( $tax > self::EPS && $vatOutput ) {
                $legs[] = $this->leg( $vatOutput, $dr, $tax );
            }

            return $legs;
        }

        /** Target COGS/inventory legs (product cost + service-consumed stock cost). */
        private function cogsTargetLegs(Order $order, array $d) : array
        {
            if ( ! $this->isPostableSale( $order ) ) {
                return [];
            }

            $cogs      = $this->acc( $d, 'cogs' );
            $inventory = $this->acc( $d, 'inventory' );
            if ( ! $cogs || ! $inventory ) {
                return [];
            }

            $cost = (float) $order->totalCost() + $this->serviceConsumptionCost( $order );
            if ( $cost <= self::EPS ) {
                return []; // service-only (no stock) sale or no cost data
            }

            $isReturn = ! empty( $order->original_order_id );

            return $isReturn
                ? [ $this->leg( $inventory, FALSE, $cost ), $this->leg( $cogs, TRUE, $cost ) ]
                : [ $this->leg( $cogs, FALSE, $cost ), $this->leg( $inventory, TRUE, $cost ) ];
        }

        /**
         * Split the order's net revenue into [product, service] portions by their
         * gross-line ratio (the order-level discount is allocated proportionally).
         * Falls back to all-product when no service-revenue account is configured or
         * the order has no service lines.
         *
         * @return array{0: float, 1: float}
         */
        private function splitRevenue(Order $order, float $net, bool $hasServiceAccount) : array
        {
            if ( ! $hasServiceAccount ) {
                return [ $net, 0.0 ];
            }

            $serviceGross = (float) ( $order->relationLoaded( 'orderServiceProducts' )
                ? $order->orderServiceProducts->sum( 'total' )
                : $order->orderServiceProducts()->sum( 'total' ) );
            if ( $serviceGross <= self::EPS ) {
                return [ $net, 0.0 ];
            }

            $productGross = (float) ( $order->relationLoaded( 'orderProducts' )
                ? $order->orderProducts->sum( 'total' )
                : $order->orderProducts()->sum( 'total' ) );
            $gross = $productGross + $serviceGross;
            if ( $gross <= self::EPS ) {
                return [ $net, 0.0 ];
            }

            $serviceNet = round( $net * ( $serviceGross / $gross ), 4 );

            return [ $net - $serviceNet, $serviceNet ];
        }

        /** Cost of stock consumed by the order's services (not counted in Order::totalCost). */
        private function serviceConsumptionCost(Order $order) : float
        {
            $services = $order->relationLoaded( 'orderServiceProducts' )
                ? $order->orderServiceProducts
                : $order->orderServiceProducts()->get();

            $total = 0.0;
            foreach ( $services as $osp ) {
                $service = $osp->service;
                if ( ! $service ) {
                    continue;
                }
                foreach ( $service->serviceProducts as $item ) {
                    $product = $item->product;
                    if ( ! $product ) {
                        continue;
                    }
                    $total += (float) ( $product->buying_price ?? 0 ) * (float) $item->quantity * (float) $osp->quantity;
                }
            }

            return $total;
        }

        private function saleType(Order $order) : string
        {
            if ( ! empty( $order->original_order_id ) ) {
                return 'CN';
            }
            $balance = ( (float) $order->subtotal - (float) $order->discount + (float) $order->tax ) - (float) $order->paid;

            return $balance > self::EPS ? 'IN' : 'CS';
        }

        /**
         * Expense → debit the expense account, credit bank (paid) and accrual (unpaid).
         * Reconciled so edits to the amount/paid restate the ledger (delta only).
         */
        public function postExpense(Expense $expense) : void
        {
            $this->reconcile( 'expense', $expense->id, $this->expenseTargetLegs( $expense ),
                $this->date( $expense->date ?? $expense->created_at ), $expense->name ?? 'Expense',
                $expense->branch_id, 'JN' );
        }

        /** Remove an expense from the ledger (deleted) by reconciling to nil. */
        public function voidExpense(Expense $expense) : void
        {
            $this->reconcile( 'expense', $expense->id, [], $this->date( $expense->date ?? $expense->created_at ),
                'Expense void #' . $expense->id, $expense->branch_id, 'JN' );
        }

        private function expenseTargetLegs(Expense $expense) : array
        {
            $d          = $this->defaults();
            $expenseAcc = $this->acc( $d, 'generalExpense' );
            $bank       = $this->acc( $d, 'bank' );
            $accrued    = $this->acc( $d, 'accruedLiability' ) ?? $this->acc( $d, 'payable' );
            if ( ! $expenseAcc || ! $bank ) {
                return [];
            }

            $amount = (float) $expense->amount;
            $paid   = (float) $expense->paid;
            $unpaid = $amount - $paid;

            $legs = [ $this->leg( $expenseAcc, FALSE, $amount ) ];
            if ( $paid > self::EPS ) {
                $legs[] = $this->leg( $bank, TRUE, $paid );
            }
            if ( $unpaid > self::EPS && $accrued ) {
                $legs[] = $this->leg( $accrued, TRUE, $unpaid );
            }

            return $legs;
        }

        /** Customer wallet deposit/withdrawal between bank and the wallet liability. */
        public function postWallet(CustomerWalletTransaction $wallet) : void
        {
            $d          = $this->defaults();
            $bank       = $this->methodAccount( $d, $wallet->payment_method_id ?? NULL );
            $walletAcc  = $this->acc( $d, 'customerWallet' );
            if ( ! $bank || ! $walletAcc ) {
                return;
            }

            $amount    = (float) $wallet->amount;
            $type      = $wallet->type instanceof CustomerWalletTransactionType
                ? $wallet->type
                : CustomerWalletTransactionType::tryFrom( (int) $wallet->type );
            $isDeposit = $type === CustomerWalletTransactionType::DEPOSIT;

            $legs = $isDeposit
                ? [ $this->leg( $bank, FALSE, $amount ), $this->leg( $walletAcc, TRUE, $amount ) ]
                : [ $this->leg( $walletAcc, FALSE, $amount ), $this->leg( $bank, TRUE, $amount ) ];

            $this->emit( 'wallet', $wallet->id, $this->date( $wallet->created_at ),
                'Customer wallet ' . ( $isDeposit ? 'deposit' : 'withdrawal' ), $legs, $wallet->branch_id, 'JN' );
        }

        /**
         * Supplier purchase → a bill: debit inventory/expense/asset + input VAT,
         * credit payable. Reconciled so edits to the bill restate the ledger.
         */
        public function postPurchase(Purchase $purchase) : void
        {
            $this->reconcile( 'purchase', $purchase->id, $this->purchaseTargetLegs( $purchase ),
                $this->date( $purchase->created_at ), 'Purchase #' . $purchase->id, $purchase->branch_id, 'BL' );
        }

        /** Remove a purchase bill from the ledger (deleted) by reconciling to nil. */
        public function voidPurchase(Purchase $purchase) : void
        {
            $this->reconcile( 'purchase', $purchase->id, [], $this->date( $purchase->created_at ),
                'Purchase void #' . $purchase->id, $purchase->branch_id, 'BL' );
        }

        private function purchaseTargetLegs(Purchase $purchase) : array
        {
            if ( ! $purchase->supplier_id ) {
                return []; // internal stock transfer, not a supplier purchase
            }

            $d        = $this->defaults();
            $payable  = $this->acc( $d, 'payable' );
            $vatInput = $this->acc( $d, 'vatInput' );
            if ( ! $payable ) {
                return [];
            }

            $type      = $purchase->type instanceof PurchaseType ? $purchase->type->value : (int) $purchase->type;
            $targetKey = match ( $type ) {
                PurchaseType::ASSET_PURCHASE->value => 'fixedAsset',
                PurchaseType::EXPENSE->value        => 'generalExpense',
                default                             => 'inventory',
            };
            $target = $this->acc( $d, $targetKey );
            if ( ! $target ) {
                return [];
            }

            $net = (float) $purchase->subtotal - (float) $purchase->discount;
            $tax = (float) $purchase->tax;

            $legs = [];
            if ( $net > self::EPS ) {
                $legs[] = $this->leg( $target, FALSE, $net );
            }
            if ( $tax > self::EPS && $vatInput ) {
                $legs[] = $this->leg( $vatInput, FALSE, $tax );
            }
            $payableAmount = $net + $tax;
            if ( $payableAmount > self::EPS ) {
                $legs[] = $this->leg( $payable, TRUE, $payableAmount );
            }

            return $legs;
        }

        /** Payment to a supplier → debit payable, credit bank. */
        public function postPurchasePayment(PurchasePayment $payment) : void
        {
            $d       = $this->defaults();
            $payable = $this->acc( $d, 'payable' );
            $bank    = $this->methodAccount( $d, $payment->payment_method ?? NULL );
            if ( ! $payable || ! $bank ) {
                return;
            }

            $amount = (float) $payment->amount;
            $legs   = [ $this->leg( $payable, FALSE, $amount ), $this->leg( $bank, TRUE, $amount ) ];

            $this->emit( 'purchase_payment', $payment->id, $this->date( $payment->created_at ),
                'Supplier payment', $legs, $payment->branch_id, 'PY' );
        }

        /** Customer debt payment (DEBT) → debit bank, credit receivable; WITHDRAW reverses. */
        public function postCustomerPayment(CustomerPayment $payment) : void
        {
            $d          = $this->defaults();
            $receivable = $this->acc( $d, 'receivable' );
            $bank       = $this->methodAccount( $d, $payment->payment_method_id ?? NULL );
            if ( ! $receivable || ! $bank ) {
                return;
            }

            $amount = (float) $payment->amount;
            $type   = $payment->customer_payment_type instanceof CustomerPaymentType
                ? $payment->customer_payment_type
                : CustomerPaymentType::tryFrom( (int) $payment->customer_payment_type );
            $isDebt = $type === CustomerPaymentType::DEBT;

            $legs = $isDebt
                ? [ $this->leg( $bank, FALSE, $amount ), $this->leg( $receivable, TRUE, $amount ) ]
                : [ $this->leg( $receivable, FALSE, $amount ), $this->leg( $bank, TRUE, $amount ) ];

            $this->emit( 'customer_payment', $payment->id, $this->date( $payment->created_at ),
                'Customer ' . ( $isDebt ? 'debt payment' : 'withdrawal' ), $legs, $payment->branch_id, 'RC' );
        }

        /** A later payment on a credit expense → debit the accrual, credit bank. */
        public function postExpensePayment(ExpensePayment $payment) : void
        {
            $d       = $this->defaults();
            $accrued = $this->acc( $d, 'accruedLiability' ) ?? $this->acc( $d, 'payable' );
            $bank    = $this->methodAccount( $d, $payment->payment_method_id ?? NULL );
            if ( ! $accrued || ! $bank ) {
                return;
            }

            $amount = (float) $payment->amount;
            $legs   = [ $this->leg( $accrued, FALSE, $amount ), $this->leg( $bank, TRUE, $amount ) ];

            $this->emit( 'expense_payment', $payment->id, $this->date( $payment->date ?? $payment->created_at ),
                'Expense payment', $legs, $payment->branch_id, 'PY' );
        }

        /**
         * Inventory write-off (damage / expiry / shrinkage) → Dr loss, Cr inventory.
         * Reconciled + status-gated: a Rejected damage posts nothing (and reverses a
         * prior write-off if it was approved then rejected); edits restate the value.
         */
        public function postDamage(Damage $damage) : void
        {
            $this->reconcile( 'damage', $damage->id, $this->damageTargetLegs( $damage ),
                $this->date( $damage->date ?? $damage->created_at ),
                'Inventory write-off ' . ( $damage->reference_no ?? '#' . $damage->id ), $damage->branch_id, 'JN' );
        }

        /** Remove a damage write-off from the ledger (deleted) by reconciling to nil. */
        public function voidDamage(Damage $damage) : void
        {
            $this->reconcile( 'damage', $damage->id, [], $this->date( $damage->date ?? $damage->created_at ),
                'Damage void #' . $damage->id, $damage->branch_id, 'JN' );
        }

        private function damageTargetLegs(Damage $damage) : array
        {
            $status = $damage->status instanceof DamageStatus ? $damage->status : DamageStatus::tryFrom( (string) $damage->status );
            if ( $status === DamageStatus::Rejected ) {
                return []; // rejected write-offs do not hit the ledger
            }

            $d         = $this->defaults();
            $loss      = $this->acc( $d, 'inventoryLoss' ) ?? $this->acc( $d, 'generalExpense' );
            $inventory = $this->acc( $d, 'inventory' );
            if ( ! $loss || ! $inventory ) {
                return [];
            }

            $amount = (float) $damage->total;
            if ( $amount <= self::EPS ) {
                return [];
            }

            return [ $this->leg( $loss, FALSE, $amount ), $this->leg( $inventory, TRUE, $amount ) ];
        }

        /**
         * Production completion → reclassify cost from raw materials to finished
         * goods: Dr finished goods inventory / Cr raw materials inventory, at the
         * batch's recipe cost (per-unit material cost × actual quantity produced).
         * Skipped when raw/finished map to the same inventory account (no net effect)
         * or when there is no cost data.
         */
        public function postProduction(ProductionProcess $process) : void
        {
            $this->reconcile( 'production', $process->id, $this->productionTargetLegs( $process ),
                $this->date( $process->end_date ?? $process->created_at ),
                'Production completion #' . $process->id, $process->branch_id ?? NULL, 'JN' );
        }

        /** Remove a production posting from the ledger (deleted) by reconciling to nil. */
        public function voidProduction(ProductionProcess $process) : void
        {
            $this->reconcile( 'production', $process->id, [], $this->date( $process->end_date ?? $process->created_at ),
                'Production void #' . $process->id, $process->branch_id ?? NULL, 'JN' );
        }

        private function productionTargetLegs(ProductionProcess $process) : array
        {
            // Only a completed batch moves cost into finished goods; a cancelled or
            // reverted batch yields an empty target, which reverses any prior posting.
            if ( (int) $process->status !== ProductionProcessStatus::COMPLETED ) {
                return [];
            }

            $d        = $this->defaults();
            $raw      = $this->acc( $d, 'rawMaterials' );
            $finished = $this->acc( $d, 'finishedGoods' );
            if ( ! $raw || ! $finished || $raw === $finished ) {
                return [];
            }

            $product = $process->setup?->product;
            $qty     = (float) ( $process->actual_quantity ?? 0 );
            if ( ! $product || $qty <= 0 ) {
                return [];
            }

            $perUnit = (float) $product->rawMaterials()->sum( 'total' );
            $cost    = $perUnit * $qty;
            if ( $cost <= self::EPS ) {
                return [];
            }

            return [ $this->leg( $finished, FALSE, $cost ), $this->leg( $raw, TRUE, $cost ) ];
        }

        /** Commission paid out to staff/agents → Dr commission expense, Cr bank. */
        public function postCommissionPayout(CommissionPayout $payout) : void
        {
            $d          = $this->defaults();
            $commission = $this->acc( $d, 'commissions' ) ?? $this->acc( $d, 'generalExpense' );
            $bank       = $this->acc( $d, 'bank' );
            if ( ! $commission || ! $bank ) {
                return;
            }

            $amount = (float) $payout->amount;
            $legs   = [ $this->leg( $commission, FALSE, $amount ), $this->leg( $bank, TRUE, $amount ) ];

            $this->emit( 'commission_payout', $payout->id, $this->date( $payout->date ?? $payout->created_at ),
                'Commission payout ' . ( $payout->reference ?? '#' . $payout->id ), $legs, $payout->branch_id ?? NULL, 'PY' );
        }

        /**
         * Post reversing entries for an operational record that was voided/deleted.
         * Reads each already-posted transaction by (source, source_id) and posts its
         * mirror image (every leg's debit/credit flipped). Idempotent: a second call
         * is a no-op because the reversal carries its own "{source}_reversal" source.
         *
         * @param string[] $sources
         */
        public function reverse(array $sources, int|string $sourceId, ?string $date = NULL) : void
        {
            AccountingContext::ensure();

            foreach ( $sources as $source ) {
                $txn = Transaction::with( 'lineItems' )
                                  ->where( 'source', $source )
                                  ->where( 'source_id', (string) $sourceId )
                                  ->first();
                if ( ! $txn ) {
                    continue;
                }

                $legs = [ $this->leg( (int) $txn->account_id, ! (bool) $txn->credited, (float) $txn->main_account_amount ) ];
                foreach ( $txn->lineItems as $li ) {
                    $legs[] = $this->leg( (int) $li->account_id, ! (bool) $li->credited, (float) $li->amount );
                }

                $this->emit( $source . '_reversal', $sourceId, $date ?: date( 'Y-m-d' ),
                    'Reversal — ' . ( $txn->narration ?: $source ), $legs, $txn->branch_id, 'JN' );
            }
        }

        /* ------------------------------- internals ------------------------------ */

        /** Post once per (source, source_id) — for events that never change after creation. */
        private function emit(string $source, int|string $sourceId, string $date, string $narration, array $legs, $branchId, string $type) : void
        {
            AccountingContext::ensure();

            if ( $this->alreadyPosted( $source, $sourceId ) ) {
                return;
            }

            $this->postCompound( $source, $sourceId, $date, $narration, $legs, $branchId, $type );
        }

        /**
         * Post the DELTA needed to make the ledger match `$targetLegs` for this
         * (source, source_id). Reads everything already posted for the source, nets
         * it per account, and posts only the difference — so it works for the first
         * posting, an amount change (edit / conversion) and a void (empty target),
         * and is naturally idempotent (no change → nothing posted). Used for editable
         * documents (sales) where `emit`'s post-once rule is not enough.
         */
        private function reconcile(string $source, int|string $sourceId, array $targetLegs, string $date, string $narration, $branchId, string $type) : void
        {
            AccountingContext::ensure();

            $posted = $this->postedNet( $source, $sourceId );

            $target = [];
            foreach ( $targetLegs as $l ) {
                if ( $l[ 'amount' ] <= self::EPS ) {
                    continue;
                }
                $signed = $l[ 'credited' ] ? -$l[ 'amount' ] : $l[ 'amount' ];
                $target[ $l[ 'accountId' ] ] = ( $target[ $l[ 'accountId' ] ] ?? 0 ) + $signed;
            }

            $accounts = array_unique( array_merge( array_keys( $posted ), array_keys( $target ) ) );

            $legs = [];
            foreach ( $accounts as $accountId ) {
                $delta = ( $target[ $accountId ] ?? 0 ) - ( $posted[ $accountId ] ?? 0 );
                if ( abs( $delta ) > self::EPS ) {
                    $legs[] = $this->leg( (int) $accountId, $delta < 0, abs( $delta ) );
                }
            }

            if ( count( $legs ) < 2 ) {
                return; // no change (or already balanced)
            }

            $this->postCompound( $source, $sourceId, $date, $narration, $legs, $branchId, $type );
        }

        /** Net the already-posted ledger for a source into [accountId => signed amount] (debit +, credit −). */
        private function postedNet(string $source, int|string $sourceId) : array
        {
            $net = [];

            Transaction::with( 'lineItems' )
                       ->where( 'source', $source )
                       ->where( 'source_id', (string) $sourceId )
                       ->get()
                       ->each( function (Transaction $t) use ( &$net ) {
                           $sign                          = $t->credited ? -1 : 1;
                           $net[ (int) $t->account_id ]   = ( $net[ (int) $t->account_id ] ?? 0 ) + $sign * (float) $t->main_account_amount;
                           foreach ( $t->lineItems as $li ) {
                               $s                          = $li->credited ? -1 : 1;
                               $net[ (int) $li->account_id ] = ( $net[ (int) $li->account_id ] ?? 0 ) + $s * (float) $li->amount;
                           }
                       } );

            return $net;
        }

        private function postCompound(string $source, int|string $sourceId, string $date, string $narration, array $legs, $branchId, string $type) : void
        {
            $legs = array_values( array_filter( $legs, static fn (array $l) => abs( $l[ 'amount' ] ) > self::EPS ) );
            if ( count( $legs ) < 2 ) {
                return;
            }

            try {
                $this->posting->post( [
                    'transactionType' => $type,
                    'date'            => $date,
                    'narration'       => $narration,
                    'accountId'       => $legs[ 0 ][ 'accountId' ],
                    'credited'        => $legs[ 0 ][ 'credited' ],
                    'compound'        => TRUE,
                    'lineItems'       => array_map( static fn (array $l) => [
                        'accountId' => $l[ 'accountId' ],
                        'amount'    => $l[ 'amount' ],
                        'credited'  => $l[ 'credited' ],
                        'quantity'  => 1,
                    ], $legs ),
                    'branchId'        => $branchId ? (string) $branchId : NULL,
                    'source'          => $source,
                    'sourceId'        => (string) $sourceId,
                ] );
            } catch ( ClosedPeriodException $e ) {
                // The operational change happened but its period is closed — don't lose
                // it silently; record an alert so the accountant posts an adjustment.
                $this->recordAlert( $source, $sourceId, $date, $narration, $e->getMessage() );
            }
        }

        private function recordAlert(string $source, int|string $sourceId, string $date, string $narration, string $message) : void
        {
            try {
                PostingAlert::updateOrCreate(
                    [ 'source' => $source, 'source_id' => (string) $sourceId ],
                    [ 'posting_date' => $date, 'narration' => $narration, 'message' => $message, 'resolved_at' => NULL ],
                );
            } catch ( \Throwable $e ) {
                // Alerting must never break the operational flow.
            }
        }

        private function alreadyPosted(string $source, int|string $sourceId) : bool
        {
            return Transaction::query()
                              ->where( 'source', $source )
                              ->where( 'source_id', (string) $sourceId )
                              ->exists();
        }

        private function defaults() : array
        {
            return Settings::group( 'accounting' )->get( 'default_accounts' ) ?: [];
        }

        private function acc(array $defaults, string $key) : ?int
        {
            return ! empty( $defaults[ $key ] ) ? (int) $defaults[ $key ] : NULL;
        }

        /**
         * Resolve the cash/bank account that settles a payment, from the tenant's
         * payment-method → account map (Settings 'accounting.payment_method_accounts').
         * An unmapped or missing method falls back to the default bank account, so a
         * tenant that never configures the map keeps the original behaviour.
         */
        private function methodAccount(array $defaults, int|string|null $methodId) : ?int
        {
            if ( $methodId !== NULL && $methodId !== '' ) {
                $map = $this->paymentMethodMap();
                if ( ! empty( $map[ (string) $methodId ] ) ) {
                    return (int) $map[ (string) $methodId ];
                }
            }

            return $this->acc( $defaults, 'bank' );
        }

        private function paymentMethodMap() : array
        {
            if ( $this->methodMap === NULL ) {
                $this->methodMap = Settings::group( 'accounting' )->get( 'payment_method_accounts' ) ?: [];
            }

            return $this->methodMap;
        }

        private function leg(int $accountId, bool $credited, float $amount) : array
        {
            return [ 'accountId' => $accountId, 'credited' => $credited, 'amount' => $amount ];
        }

        private function date(mixed $value) : string
        {
            return $value ? Carbon::parse( $value )->toDateString() : date( 'Y-m-d' );
        }
    }
