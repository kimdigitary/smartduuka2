<?php

    namespace App\Services\Accounting;

    use App\Support\Accounting\AccountTypes;
    use IFRS\Models\Account;
    use IFRS\Models\Transaction;
    use Illuminate\Database\Eloquent\Collection;
    use Smartisan\Settings\Facades\Settings;

    /**
     * Financial reports computed directly from the posted ledger, mirroring the
     * frontend `lib/ifrs/reports.ts` exactly so output matches 1:1. Every entry is
     * reconstructed from the transaction's legs (main account + line items), the
     * same shape PostingService writes, so the trial balance always balances.
     */
    class ReportService
    {
        private const EPS = 0.005;

        /** Transaction types that create clearable open items (AR invoices / AP bills). */
        private const CLEARABLE = [ 'IN', 'BL' ];

        private ?Collection $accountCache = NULL;

        /* ------------------------------- movements ------------------------------ */

        /** @return array<int, array{debit:float,credit:float}> */
        public function movements(?string $start = NULL, ?string $end = NULL, ?string $branchId = NULL) : array
        {
            $m = [];
            foreach ( $this->transactions( $start, $end, $branchId ) as $t ) {
                foreach ( $this->legs( $t ) as $leg ) {
                    $id      = $leg[ 'accountId' ];
                    $m[ $id ] ??= [ 'debit' => 0.0, 'credit' => 0.0 ];
                    if ( $leg[ 'credited' ] ) {
                        $m[ $id ][ 'credit' ] += $leg[ 'amount' ];
                    } else {
                        $m[ $id ][ 'debit' ] += $leg[ 'amount' ];
                    }
                }
            }

            return $m;
        }

        /* ----------------------------- trial balance ---------------------------- */

        public function trialBalance(?string $end = NULL) : array
        {
            $mv          = $this->movements( NULL, $end );
            $rows        = [];
            $totalDebit  = 0.0;
            $totalCredit = 0.0;

            foreach ( $this->accounts() as $a ) {
                $m = $mv[ $a->id ] ?? NULL;
                if ( ! $m ) {
                    continue;
                }
                $net = $m[ 'debit' ] - $m[ 'credit' ];
                if ( abs( $net ) < self::EPS ) {
                    continue;
                }
                $debit  = $net > 0 ? $net : 0.0;
                $credit = $net < 0 ? -$net : 0.0;
                $rows[] = [ 'account' => $this->accountArr( $a ), 'debit' => $debit, 'credit' => $credit ];
                $totalDebit  += $debit;
                $totalCredit += $credit;
            }

            return [
                'rows'        => $rows,
                'totalDebit'  => $totalDebit,
                'totalCredit' => $totalCredit,
                'balanced'    => abs( $totalDebit - $totalCredit ) < 0.5,
            ];
        }

        /* --------------------------- income statement --------------------------- */

        public function incomeStatement(?string $start = NULL, ?string $end = NULL) : array
        {
            $mv           = $this->movements( $start, $end );
            $revenue      = [];
            $expenses     = [];
            $totalRevenue = 0.0;
            $totalExpense = 0.0;

            foreach ( $this->accounts() as $a ) {
                $m = $mv[ $a->id ] ?? NULL;
                if ( ! $m ) {
                    continue;
                }
                $group = AccountTypes::group( $a->account_type );
                if ( $group === 'INCOME' ) {
                    $amt = $m[ 'credit' ] - $m[ 'debit' ];
                    if ( abs( $amt ) >= self::EPS ) {
                        $revenue[]    = [ 'account' => $this->accountArr( $a ), 'amount' => $amt ];
                        $totalRevenue += $amt;
                    }
                } elseif ( $group === 'EXPENSE' ) {
                    $amt = $m[ 'debit' ] - $m[ 'credit' ];
                    if ( abs( $amt ) >= self::EPS ) {
                        $expenses[]   = [ 'account' => $this->accountArr( $a ), 'amount' => $amt ];
                        $totalExpense += $amt;
                    }
                }
            }

            return [
                'revenue'      => $revenue,
                'expenses'     => $expenses,
                'totalRevenue' => $totalRevenue,
                'totalExpense' => $totalExpense,
                'netProfit'    => $totalRevenue - $totalExpense,
            ];
        }

        /* ----------------------------- balance sheet ---------------------------- */

        public function balanceSheet(?string $end = NULL) : array
        {
            $mv              = $this->movements( NULL, $end );
            $assets          = [];
            $liabilities     = [];
            $equity          = [];
            $totalAssets     = 0.0;
            $totalLiabilities = 0.0;
            $equitySum       = 0.0;

            foreach ( $this->accounts() as $a ) {
                $m = $mv[ $a->id ] ?? NULL;
                if ( ! $m ) {
                    continue;
                }
                $group = AccountTypes::group( $a->account_type );
                if ( $group === 'ASSETS' ) {
                    $amt = $m[ 'debit' ] - $m[ 'credit' ];
                    if ( abs( $amt ) >= self::EPS ) {
                        $assets[]    = [ 'account' => $this->accountArr( $a ), 'amount' => $amt ];
                        $totalAssets += $amt;
                    }
                } elseif ( $group === 'LIABILITIES' ) {
                    $amt = $m[ 'credit' ] - $m[ 'debit' ];
                    if ( abs( $amt ) >= self::EPS ) {
                        $liabilities[]    = [ 'account' => $this->accountArr( $a ), 'amount' => $amt ];
                        $totalLiabilities += $amt;
                    }
                } elseif ( $group === 'EQUITY' ) {
                    $amt = $m[ 'credit' ] - $m[ 'debit' ];
                    if ( abs( $amt ) >= self::EPS ) {
                        $equity[]  = [ 'account' => $this->accountArr( $a ), 'amount' => $amt ];
                        $equitySum += $amt;
                    }
                }
            }

            $netProfit   = $this->incomeStatement( NULL, $end )[ 'netProfit' ];
            $totalEquity = $equitySum + $netProfit;

            return [
                'assets'           => $assets,
                'liabilities'      => $liabilities,
                'equity'           => $equity,
                'netProfit'        => $netProfit,
                'totalAssets'      => $totalAssets,
                'totalLiabilities' => $totalLiabilities,
                'totalEquity'      => $totalEquity,
                'balanced'         => abs( $totalAssets - ( $totalLiabilities + $totalEquity ) ) < 0.5,
            ];
        }

        /* ---------------------------- cash flow --------------------------------- */

        public function cashFlow(?string $start = NULL, ?string $end = NULL) : array
        {
            $typeOf  = [];
            $bankIds = [];
            foreach ( $this->accounts() as $a ) {
                $typeOf[ $a->id ] = $a->account_type;
                if ( $a->account_type === 'BANK' ) {
                    $bankIds[ $a->id ] = TRUE;
                }
            }

            $operating = 0.0;
            $investing = 0.0;
            $financing = 0.0;

            foreach ( $this->transactions( $start, $end ) as $t ) {
                $legs    = $this->legs( $t );
                $bankNet = 0.0;
                foreach ( $legs as $l ) {
                    if ( isset( $bankIds[ $l[ 'accountId' ] ] ) ) {
                        $bankNet += $l[ 'credited' ] ? -$l[ 'amount' ] : $l[ 'amount' ];
                    }
                }
                if ( abs( $bankNet ) < self::EPS ) {
                    continue;
                }

                $otherTypes = [];
                foreach ( $legs as $l ) {
                    if ( ! isset( $bankIds[ $l[ 'accountId' ] ] ) ) {
                        $otherTypes[] = $typeOf[ $l[ 'accountId' ] ] ?? NULL;
                    }
                }
                $investingHit = (bool) array_intersect( $otherTypes, [ 'NON_CURRENT_ASSET', 'CONTRA_ASSET' ] );
                $financingHit = (bool) array_intersect( $otherTypes, [ 'EQUITY', 'NON_CURRENT_LIABILITY' ] );

                if ( $investingHit ) {
                    $investing += $bankNet;
                } elseif ( $financingHit ) {
                    $financing += $bankNet;
                } else {
                    $operating += $bankNet;
                }
            }

            $netChange = $operating + $investing + $financing;

            $mvAll   = $this->movements( NULL, $end );
            $closing = 0.0;
            foreach ( array_keys( $bankIds ) as $id ) {
                $m = $mvAll[ $id ] ?? NULL;
                if ( $m ) {
                    $closing += $m[ 'debit' ] - $m[ 'credit' ];
                }
            }

            return [
                'operating' => $operating,
                'investing' => $investing,
                'financing' => $financing,
                'netChange' => $netChange,
                'opening'   => $closing - $netChange,
                'closing'   => $closing,
            ];
        }

        /* -------------------------- account statement --------------------------- */

        public function accountStatement(int $accountId, ?string $start = NULL, ?string $end = NULL) : array
        {
            $opening = 0.0;
            $running = 0.0;
            $entries = [];

            foreach ( $this->transactions() as $t ) {
                $legForAccount = array_values( array_filter(
                    $this->legs( $t ),
                    static fn (array $l) => $l[ 'accountId' ] === $accountId,
                ) );
                if ( ! $legForAccount ) {
                    continue;
                }

                $debit  = 0.0;
                $credit = 0.0;
                foreach ( $legForAccount as $l ) {
                    if ( $l[ 'credited' ] ) {
                        $credit += $l[ 'amount' ];
                    } else {
                        $debit += $l[ 'amount' ];
                    }
                }
                $net  = $debit - $credit;
                $date = optional( $t->transaction_date )->toDateString();

                if ( $start && $date < $start ) {
                    $opening += $net;
                    $running = $opening;
                    continue;
                }
                if ( $end && $date > $end ) {
                    continue;
                }
                $running   += $net;
                $entries[] = [
                    'transaction' => $this->txnBrief( $t ),
                    'debit'       => $debit,
                    'credit'      => $credit,
                    'balance'     => $running,
                ];
            }

            return [ 'opening' => $opening, 'entries' => $entries, 'closing' => $running ];
        }

        /* ------------------------------ VAT return ------------------------------ */

        public function vatReturn(?string $start = NULL, ?string $end = NULL) : array
        {
            $map     = Settings::group( 'accounting' )->get( 'default_accounts' ) ?: [];
            $outId   = (int) ( $map[ 'vatOutput' ] ?? 0 );
            $inId    = (int) ( $map[ 'vatInput' ] ?? 0 );
            $mv      = $this->movements( $start, $end );
            $out     = $mv[ $outId ] ?? [ 'debit' => 0.0, 'credit' => 0.0 ];
            $inp     = $mv[ $inId ] ?? [ 'debit' => 0.0, 'credit' => 0.0 ];
            $output  = $out[ 'credit' ] - $out[ 'debit' ];
            $input   = $inp[ 'debit' ] - $inp[ 'credit' ];

            return [
                'outputVat'  => $output,
                'inputVat'   => $input,
                'netPayable' => $output - $input,
            ];
        }

        /* ----------------------------- aging schedule --------------------------- */

        public function aging(string $accountType, ?string $asOf = NULL) : array
        {
            $asOf    = $asOf ?: date( 'Y-m-d' );
            $asOfTs  = strtotime( $asOf );
            $ids     = [];
            foreach ( $this->accounts() as $a ) {
                if ( $a->account_type === $accountType ) {
                    $ids[ $a->id ] = TRUE;
                }
            }

            $buckets = [ 0.0, 0.0, 0.0, 0.0, 0.0 ];
            $rows    = [];

            foreach ( $this->transactions( NULL, $asOf ) as $t ) {
                if ( ! isset( $ids[ (int) $t->account_id ] ) ) {
                    continue;
                }
                if ( ! in_array( $t->transaction_type, self::CLEARABLE, TRUE ) ) {
                    continue;
                }
                // Outstanding = full amount until assignments/clearing is implemented.
                $due = (float) ( $t->compound ? $t->main_account_amount : $this->mainSum( $t ) );
                if ( $due < self::EPS ) {
                    continue;
                }
                $ageDays = max( 0, (int) round( ( $asOfTs - strtotime( optional( $t->transaction_date )->toDateString() ) ) / 86400 ) );
                $bucket  = $ageDays <= 30 ? 0 : ( $ageDays <= 60 ? 1 : ( $ageDays <= 90 ? 2 : ( $ageDays <= 120 ? 3 : 4 ) ) );
                $buckets[ $bucket ] += $due;
                $rows[]            = [
                    'transaction' => $this->txnBrief( $t ),
                    'outstanding' => $due,
                    'ageDays'     => $ageDays,
                    'bucket'      => $bucket,
                ];
            }

            usort( $rows, static fn (array $a, array $b) => $b[ 'ageDays' ] <=> $a[ 'ageDays' ] );

            return [
                'rows'         => $rows,
                'buckets'      => $buckets,
                'total'        => array_sum( $buckets ),
                'bucketLabels' => [ 'Current', '31–60', '61–90', '91–120', '120+' ],
            ];
        }

        /* --------------------------- account balances --------------------------- */

        /** @return array<int, float> account id => balance on its normal side */
        public function accountBalances(?string $end = NULL) : array
        {
            $mv  = $this->movements( NULL, $end );
            $out = [];
            foreach ( $this->accounts() as $a ) {
                $m = $mv[ $a->id ] ?? NULL;
                if ( ! $m ) {
                    $out[ $a->id ] = 0.0;
                    continue;
                }
                $out[ $a->id ] = AccountTypes::normalBalance( $a->account_type ) === 'D'
                    ? $m[ 'debit' ] - $m[ 'credit' ]
                    : $m[ 'credit' ] - $m[ 'debit' ];
            }

            return $out;
        }

        /* -------------------------------- helpers ------------------------------- */

        private function transactions(?string $start = NULL, ?string $end = NULL, ?string $branchId = NULL) : Collection
        {
            return Transaction::with( 'lineItems' )
                              ->when( $start, fn ($q) => $q->whereDate( 'transaction_date', '>=', $start ) )
                              ->when( $end, fn ($q) => $q->whereDate( 'transaction_date', '<=', $end ) )
                              ->when( $branchId, fn ($q) => $q->where( 'branch_id', $branchId ) )
                              ->orderBy( 'transaction_date' )
                              ->orderBy( 'id' )
                              ->get();
        }

        /** @return array<int, array{accountId:int,credited:bool,amount:float}> */
        private function legs(Transaction $t) : array
        {
            $mainAmount = (float) ( $t->compound ? $t->main_account_amount : $this->mainSum( $t ) );

            $legs = [ [
                'accountId' => (int) $t->account_id,
                'credited'  => (bool) $t->credited,
                'amount'    => $mainAmount,
            ] ];

            foreach ( $t->lineItems as $li ) {
                $legs[] = [
                    'accountId' => (int) $li->account_id,
                    'credited'  => (bool) $li->credited,
                    'amount'    => (float) $li->amount * (float) ( $li->quantity ?: 1 ),
                ];
            }

            return $legs;
        }

        private function mainSum(Transaction $t) : float
        {
            $sum = 0.0;
            foreach ( $t->lineItems as $li ) {
                $sum += (float) $li->amount * (float) ( $li->quantity ?: 1 );
            }

            return $sum;
        }

        private function accounts() : Collection
        {
            return $this->accountCache ??= Account::orderBy( 'code' )->get();
        }

        private function accountArr(Account $a) : array
        {
            return [
                'id'          => $a->id,
                'code'        => (string) $a->code,
                'name'        => $a->name,
                'accountType' => $a->account_type,
                'categoryId'  => $a->category_id,
                'currencyId'  => $a->currency_id,
                'isActive'    => (bool) ( $a->is_active ?? TRUE ),
            ];
        }

        private function txnBrief(Transaction $t) : array
        {
            return [
                'id'              => $t->id,
                'transactionType' => $t->transaction_type,
                'transactionNo'   => $t->transaction_no,
                'date'            => optional( $t->transaction_date )->toDateString(),
                'narration'       => $t->narration,
                'reference'       => $t->reference,
                'branchId'        => $t->branch_id,
            ];
        }
    }
