<?php

    namespace App\Http\Resources\Accounting;

    use App\Models\Accounting\RecurringJournal;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin RecurringJournal */
    class RecurringJournalResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'          => $this->id,
                'name'        => $this->name,
                'frequency'   => $this->frequency,
                'startDate'   => optional( $this->start_date )->toDateString(),
                'nextRunDate' => optional( $this->next_run_date )->toDateString(),
                'endDate'     => optional( $this->end_date )->toDateString(),
                'narration'   => $this->narration ?? '',
                'reference'   => $this->reference,
                'branchId'    => $this->branch_id,
                'active'      => (bool) $this->active,
                'lastRunDate' => optional( $this->last_run_date )->toDateString(),
                'lines'       => collect( $this->lines ?? [] )->map( static fn ($l) => [
                    'accountId' => (int) $l[ 'accountId' ],
                    'amount'    => (float) $l[ 'amount' ],
                    'credited'  => (bool) ( $l[ 'credited' ] ?? FALSE ),
                ] )->all(),
            ];
        }
    }
