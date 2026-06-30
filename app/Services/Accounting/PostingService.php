<?php

    namespace App\Services\Accounting;

    use Carbon\Carbon;
    use IFRS\Models\LineItem;
    use IFRS\Models\ReportingPeriod;
    use IFRS\Models\Transaction;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

    /**
     * Turns a frontend-shaped transaction payload into a posted IFRS transaction.
     *
     * Every entry is posted as an IFRS *compound* transaction: the first ledger
     * leg becomes the main account (account_id + credited + main_account_amount)
     * and the remaining legs become line items, each carrying its own credited
     * flag. This mirrors the frontend `transactionToLedger()` exactly, so simple
     * and compound entries are handled by one deterministic path. VAT is already
     * represented as explicit account legs, so IFRS's own VAT mechanism is unused.
     *
     * Reused by the manual journal API (Phase 4) and operational auto-posting (Phase 7).
     */
    class PostingService
    {
        private const EPSILON = 0.00001;

        public function post(array $data) : Transaction
        {
            $this->assertPeriodOpen( Carbon::parse( $data[ 'date' ] ) );

            $legs = array_values( array_filter(
                $this->toLegs( $data ),
                static fn (array $leg) => abs( $leg[ 'amount' ] ) > self::EPSILON,
            ) );

            if ( count( $legs ) < 2 ) {
                throw new \InvalidArgumentException( 'A transaction needs at least two non-zero ledger legs.' );
            }

            $currencyId = $data[ 'currencyId' ] ?? Auth::user()?->entity?->currency_id;
            $main       = $legs[ 0 ];

            return DB::transaction( function () use ( $data, $legs, $main, $currencyId ) {
                $transaction = new Transaction( [
                    'account_id'          => $main[ 'accountId' ],
                    'transaction_date'    => Carbon::parse( $data[ 'date' ] ),
                    'narration'           => $data[ 'narration' ] ?? '',
                    'currency_id'         => $currencyId,
                    'reference'           => $data[ 'reference' ] ?? NULL,
                    'transaction_type'    => $data[ 'transactionType' ] ?? Transaction::JN,
                    'credited'            => $main[ 'credited' ],
                    'compound'            => TRUE,
                    'main_account_amount' => $main[ 'amount' ],
                ] );
                $transaction->branch_id = $data[ 'branchId' ] ?? NULL;
                $transaction->source    = $data[ 'source' ] ?? 'manual';
                $transaction->source_id = isset( $data[ 'sourceId' ] ) ? (string) $data[ 'sourceId' ] : NULL;
                $transaction->save();

                foreach ( array_slice( $legs, 1 ) as $leg ) {
                    $transaction->addLineItem( new LineItem( [
                        'account_id' => $leg[ 'accountId' ],
                        'amount'     => $leg[ 'amount' ],
                        'quantity'   => 1,
                        'credited'   => $leg[ 'credited' ],
                    ] ) );
                }

                $transaction->post();

                return $transaction;
            } );
        }

        /**
         * Flatten a transaction payload into balanced ledger legs, matching the
         * frontend `transactionToLedger()` + `lineItemTotal()`.
         *
         * @return array<int, array{accountId:int, credited:bool, amount:float}>
         */
        private function toLegs(array $data) : array
        {
            $items = $data[ 'lineItems' ] ?? [];

            $legAmount = static fn (array $li) : float =>
                (float) ( $li[ 'amount' ] ?? 0 ) * (float) ( $li[ 'quantity' ] ?? 1 ) + (float) ( $li[ 'vatAmount' ] ?? 0 );

            $line = static fn (array $li) : array => [
                'accountId' => (int) $li[ 'accountId' ],
                'credited'  => (bool) ( $li[ 'credited' ] ?? FALSE ),
                'amount'    => $legAmount( $li ),
            ];

            if ( ! empty( $data[ 'compound' ] ) ) {
                return array_map( $line, $items );
            }

            $legs = [ [
                'accountId' => (int) $data[ 'accountId' ],
                'credited'  => (bool) ( $data[ 'credited' ] ?? FALSE ),
                'amount'    => array_sum( array_map( $legAmount, $items ) ),
            ] ];

            foreach ( $items as $li ) {
                $legs[] = $line( $li );
            }

            return $legs;
        }

        /** Reject posting into a CLOSED reporting period (the backend half of canPostOnDate). */
        private function assertPeriodOpen(Carbon $date) : void
        {
            $entity = Auth::user()?->entity;
            if ( ! $entity ) {
                return;
            }

            $yearStart    = (int) ( $entity->year_start ?: 1 );
            $calendarYear = (int) $date->format( 'n' ) < $yearStart
                ? (int) $date->format( 'Y' ) - 1
                : (int) $date->format( 'Y' );

            $period = ReportingPeriod::where( 'entity_id', $entity->id )
                                     ->where( 'calendar_year', $calendarYear )
                                     ->first();

            if ( $period && $period->status === ReportingPeriod::CLOSED ) {
                throw new \App\Exceptions\Accounting\ClosedPeriodException(
                    'Cannot post to the closed reporting period ' . $calendarYear . '.'
                );
            }
        }
    }
