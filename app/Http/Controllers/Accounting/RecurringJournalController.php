<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\RecurringJournalRequest;
    use App\Http\Resources\Accounting\RecurringJournalResource;
    use App\Models\Accounting\RecurringJournal;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    class RecurringJournalController extends Controller
    {
        public function index() : AnonymousResourceCollection
        {
            return RecurringJournalResource::collection(
                RecurringJournal::orderBy( 'next_run_date' )->get()
            );
        }

        public function store(RecurringJournalRequest $request) : RecurringJournalResource
        {
            return new RecurringJournalResource( $this->fill( new RecurringJournal(), $request->validated() ) );
        }

        public function update(RecurringJournalRequest $request, int $id) : RecurringJournalResource
        {
            return new RecurringJournalResource( $this->fill( RecurringJournal::findOrFail( $id ), $request->validated() ) );
        }

        public function destroy(Request $request) : JsonResponse
        {
            foreach ( (array) $request->ids as $id ) {
                RecurringJournal::find( $id )?->delete();
            }

            return response()->json();
        }

        private function fill(RecurringJournal $journal, array $data) : RecurringJournal
        {
            $journal->name          = $data[ 'name' ];
            $journal->frequency     = $data[ 'frequency' ];
            $journal->start_date    = $data[ 'startDate' ];
            $journal->next_run_date = $data[ 'nextRunDate' ];
            $journal->end_date      = $data[ 'endDate' ] ?? NULL;
            $journal->narration     = $data[ 'narration' ] ?? NULL;
            $journal->reference     = $data[ 'reference' ] ?? NULL;
            $journal->branch_id     = $data[ 'branchId' ] ?? NULL;
            $journal->active        = $data[ 'active' ] ?? TRUE;
            $journal->last_run_date = $data[ 'lastRunDate' ] ?? NULL;
            $journal->lines         = array_map( static fn ($l) => [
                'accountId' => (int) $l[ 'accountId' ],
                'amount'    => (float) $l[ 'amount' ],
                'credited'  => (bool) ( $l[ 'credited' ] ?? FALSE ),
            ], $data[ 'lines' ] );
            $journal->save();

            return $journal;
        }
    }
