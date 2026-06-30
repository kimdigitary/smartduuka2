<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Resources\Accounting\PeriodResource;
    use App\Services\Accounting\PostingService;
    use App\Services\Accounting\ReportService;
    use App\Support\Accounting\AccountTypes;
    use IFRS\Models\Account;
    use IFRS\Models\ReportingPeriod;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Auth;
    use Smartisan\Settings\Facades\Settings;

    class PeriodController extends Controller
    {
        private const EPS = 0.005;

        public function index() : AnonymousResourceCollection
        {
            return PeriodResource::collection( ReportingPeriod::orderBy( 'calendar_year' )->get() );
        }

        public function openNext() : PeriodResource
        {
            return new PeriodResource( $this->openNextPeriod() );
        }

        public function close(int $id) : PeriodResource
        {
            return new PeriodResource( $this->setStatus( $id, ReportingPeriod::CLOSED ) );
        }

        public function adjusting(int $id) : PeriodResource
        {
            return new PeriodResource( $this->setStatus( $id, ReportingPeriod::ADJUSTING ) );
        }

        public function reopen(int $id) : PeriodResource
        {
            return new PeriodResource( $this->setStatus( $id, ReportingPeriod::OPEN ) );
        }

        /**
         * Year-end close (mirrors the frontend buildClosingEntries):
         *  - post one closing journal per non-zero P&L account moving its period
         *    balance into Retained Earnings, then
         *  - lock the period and open the next one.
         * Skipped if the period is already closed (idempotent).
         */
        public function yearEndClose(int $id, ReportService $reports, PostingService $posting) : JsonResponse
        {
            $period = ReportingPeriod::findOrFail( $id );
            $entries = 0;

            if ( $period->status !== ReportingPeriod::CLOSED ) {
                $retainedId = $this->retainedEarningsId();
                if ( $retainedId ) {
                    $entries = $this->postClosingEntries( $reports, $posting, $period, $retainedId );
                }
                $period = $this->setStatus( $id, ReportingPeriod::CLOSED );
            }

            $next = $this->openNextPeriod();

            return response()->json( [
                'data' => [
                    'closed'  => new PeriodResource( $period ),
                    'next'    => new PeriodResource( $next ),
                    'entries' => $entries,
                ],
            ] );
        }

        private function postClosingEntries(ReportService $reports, PostingService $posting, ReportingPeriod $period, int $retainedId) : int
        {
            [ $start, $end ] = $this->periodDates( $period );
            $movements = $reports->movements( $start, $end );
            $accounts  = Account::all()->keyBy( 'id' );
            $count     = 0;

            foreach ( $movements as $accountId => $m ) {
                $account = $accounts[ $accountId ] ?? NULL;
                if ( ! $account || AccountTypes::statement( $account->account_type ) !== 'INCOME_STATEMENT' ) {
                    continue;
                }

                $net = $m[ 'debit' ] - $m[ 'credit' ];
                if ( abs( $net ) < self::EPS ) {
                    continue;
                }

                $amount = abs( $net );
                // net > 0 (expense debit balance): credit the account, debit RE.
                // net < 0 (revenue credit balance): debit the account, credit RE.
                $posting->post( [
                    'transactionType' => 'JN',
                    'date'            => $end,
                    'narration'       => 'Year-end close ' . $period->calendar_year . ': ' . $account->name,
                    'reference'       => 'CLOSE-' . $period->calendar_year,
                    'source'          => 'year_end',
                    'sourceId'        => $period->id . '-' . $accountId,
                    'compound'        => TRUE,
                    'lineItems'       => [
                        [ 'accountId' => (int) $accountId, 'amount' => $amount, 'credited' => $net > 0, 'quantity' => 1 ],
                        [ 'accountId' => $retainedId, 'amount' => $amount, 'credited' => $net < 0, 'quantity' => 1 ],
                    ],
                ] );
                $count++;
            }

            return $count;
        }

        private function retainedEarningsId() : ?int
        {
            $map = Settings::group( 'accounting' )->get( 'default_accounts' ) ?: [];

            return ! empty( $map[ 'retainedEarnings' ] ) ? (int) $map[ 'retainedEarnings' ] : NULL;
        }

        /** @return array{0:string,1:string} [startDate, endDate] */
        private function periodDates(ReportingPeriod $period) : array
        {
            $yearStart = (int) ( Auth::user()?->entity?->year_start ?: 1 );
            $start     = Carbon::create( $period->calendar_year, $yearStart, 1 )->startOfDay();
            $end       = $start->copy()->addYear()->subDay();

            return [ $start->toDateString(), $end->toDateString() ];
        }

        private function openNextPeriod() : ReportingPeriod
        {
            $entity = Auth::user()->entity;
            $latest = ReportingPeriod::orderByDesc( 'calendar_year' )->first();
            $year   = $latest ? (int) $latest->calendar_year + 1 : (int) date( 'Y' );

            return ReportingPeriod::firstOrCreate(
                [ 'entity_id' => $entity->id, 'calendar_year' => $year ],
                [ 'period_count' => (int) ( $latest->period_count ?? 0 ) + 1, 'status' => ReportingPeriod::OPEN ],
            );
        }

        private function setStatus(int $id, string $status) : ReportingPeriod
        {
            $period         = ReportingPeriod::findOrFail( $id );
            $period->status = $status;

            if ( $status === ReportingPeriod::CLOSED ) {
                $period->closed_at = now();
                $period->closed_by = Auth::user()?->name;
            } elseif ( $status === ReportingPeriod::OPEN ) {
                $period->closed_at = NULL;
                $period->closed_by = NULL;
            }

            $period->save();

            return $period;
        }
    }
