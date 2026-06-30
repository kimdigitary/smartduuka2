<?php

    namespace App\Services\Accounting;

    use App\Enums\CustomerPaymentType;
    use App\Enums\CustomerWalletTransactionType;
    use App\Enums\OrderType;
    use App\Enums\PurchaseType;
    use App\Models\CustomerPayment;
    use App\Models\CustomerWalletTransaction;
    use App\Models\Expense;
    use App\Models\Order;
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

        public function __construct(private readonly PostingService $posting)
        {
        }

        /** Cash/credit sale → revenue + output VAT, settled to bank and/or receivable. */
        public function postSale(Order $order) : void
        {
            $orderType = $order->order_type instanceof OrderType ? $order->order_type->value : (int) $order->order_type;
            if ( $orderType === OrderType::QUOTATION->value ) {
                return; // quotations are not financial events
            }

            $d          = $this->defaults();
            $revenue    = $this->acc( $d, 'salesRevenue' );
            $bank       = $this->acc( $d, 'bank' );
            $receivable = $this->acc( $d, 'receivable' );
            $vatOutput  = $this->acc( $d, 'vatOutput' );
            if ( ! $revenue || ! $bank || ! $receivable ) {
                return; // chart of accounts not configured
            }

            $net     = (float) $order->subtotal - (float) $order->discount;
            $tax     = (float) $order->tax;
            $paid    = (float) $order->paid;
            $total   = $net + $tax;   // computed so the entry always balances
            $balance = $total - $paid;

            $legs = [];
            if ( $paid > self::EPS ) {
                $legs[] = $this->leg( $bank, FALSE, $paid );
            }
            if ( $balance > self::EPS ) {
                $legs[] = $this->leg( $receivable, FALSE, $balance );
            }
            if ( $net > self::EPS ) {
                $legs[] = $this->leg( $revenue, TRUE, $net );
            }
            if ( $tax > self::EPS && $vatOutput ) {
                $legs[] = $this->leg( $vatOutput, TRUE, $tax );
            }

            $type = $balance > self::EPS ? 'IN' : 'CS';
            $this->emit( 'sale', $order->id, $this->date( $order->created_at ),
                'Sale ' . ( $order->order_serial_no ?? '#' . $order->id ), $legs, $order->branch_id, $type );
        }

        /** Expense → debit the expense account, credit bank (paid) and accrual (unpaid). */
        public function postExpense(Expense $expense) : void
        {
            $d          = $this->defaults();
            $expenseAcc = $this->acc( $d, 'generalExpense' );
            $bank       = $this->acc( $d, 'bank' );
            $accrued    = $this->acc( $d, 'accruedLiability' ) ?? $this->acc( $d, 'payable' );
            if ( ! $expenseAcc || ! $bank ) {
                return;
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

            $this->emit( 'expense', $expense->id, $this->date( $expense->date ?? $expense->created_at ),
                $expense->name ?? 'Expense', $legs, $expense->branch_id, 'JN' );
        }

        /** Customer wallet deposit/withdrawal between bank and the wallet liability. */
        public function postWallet(CustomerWalletTransaction $wallet) : void
        {
            $d          = $this->defaults();
            $bank       = $this->acc( $d, 'bank' );
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

        /** Supplier purchase → a bill: debit inventory/expense/asset + input VAT, credit payable. */
        public function postPurchase(Purchase $purchase) : void
        {
            if ( ! $purchase->supplier_id ) {
                return; // internal stock transfer, not a supplier purchase
            }

            $d        = $this->defaults();
            $payable  = $this->acc( $d, 'payable' );
            $vatInput = $this->acc( $d, 'vatInput' );
            if ( ! $payable ) {
                return;
            }

            $type      = $purchase->type instanceof PurchaseType ? $purchase->type->value : (int) $purchase->type;
            $targetKey = match ( $type ) {
                PurchaseType::ASSET_PURCHASE->value => 'fixedAsset',
                PurchaseType::EXPENSE->value        => 'generalExpense',
                default                             => 'inventory',
            };
            $target = $this->acc( $d, $targetKey );
            if ( ! $target ) {
                return;
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

            $this->emit( 'purchase', $purchase->id, $this->date( $purchase->created_at ),
                'Purchase #' . $purchase->id, $legs, $purchase->branch_id, 'BL' );
        }

        /** Payment to a supplier → debit payable, credit bank. */
        public function postPurchasePayment(PurchasePayment $payment) : void
        {
            $d       = $this->defaults();
            $payable = $this->acc( $d, 'payable' );
            $bank    = $this->acc( $d, 'bank' );
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
            $bank       = $this->acc( $d, 'bank' );
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

        /* ------------------------------- internals ------------------------------ */

        private function emit(string $source, int|string $sourceId, string $date, string $narration, array $legs, $branchId, string $type) : void
        {
            AccountingContext::ensure();

            if ( $this->alreadyPosted( $source, $sourceId ) ) {
                return;
            }

            $legs = array_values( array_filter( $legs, static fn (array $l) => abs( $l[ 'amount' ] ) > self::EPS ) );
            if ( count( $legs ) < 2 ) {
                return;
            }

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

        private function leg(int $accountId, bool $credited, float $amount) : array
        {
            return [ 'accountId' => $accountId, 'credited' => $credited, 'amount' => $amount ];
        }

        private function date(mixed $value) : string
        {
            return $value ? Carbon::parse( $value )->toDateString() : date( 'Y-m-d' );
        }
    }
