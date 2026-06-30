<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Models\Accounting\PostingAlert;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;

    /**
     * Posting alerts = operational changes that could not reach the ledger (currently
     * closed-period failures). The accountant reviews these and posts a manual
     * adjusting entry, then resolves the alert.
     */
    class PostingAlertController extends Controller
    {
        public function index() : JsonResponse
        {
            $alerts = PostingAlert::whereNull( 'resolved_at' )
                                  ->orderByDesc( 'updated_at' )
                                  ->get()
                                  ->map( static fn (PostingAlert $a) => [
                                      'id'          => $a->id,
                                      'source'      => $a->source,
                                      'sourceId'    => $a->source_id,
                                      'postingDate' => optional( $a->posting_date )->toDateString(),
                                      'narration'   => $a->narration,
                                      'message'     => $a->message,
                                      'createdAt'   => optional( $a->created_at )->toDateTimeString(),
                                  ] );

            return response()->json( [ 'data' => $alerts ] );
        }

        public function resolve(int $id) : JsonResponse
        {
            PostingAlert::where( 'id', $id )->update( [ 'resolved_at' => now() ] );

            return response()->json();
        }

        public function destroy(Request $request) : JsonResponse
        {
            foreach ( (array) $request->ids as $id ) {
                PostingAlert::where( 'id', $id )->update( [ 'resolved_at' => now() ] );
            }

            return response()->json();
        }
    }
