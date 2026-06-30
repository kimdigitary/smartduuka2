<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\AssignmentRequest;
    use App\Http\Resources\Accounting\AssignmentResource;
    use App\Models\Accounting\Assignment;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    class AssignmentController extends Controller
    {
        public function index() : AnonymousResourceCollection
        {
            return AssignmentResource::collection( Assignment::orderBy( 'date' )->get() );
        }

        public function store(AssignmentRequest $request) : AssignmentResource
        {
            $data = $request->validated();

            $assignment = new Assignment();
            $assignment->transaction_id = $data[ 'transactionId' ];
            $assignment->cleared_id     = $data[ 'clearedId' ];
            $assignment->amount         = $data[ 'amount' ];
            $assignment->date           = $data[ 'date' ] ?? now()->toDateString();
            $assignment->save();

            return new AssignmentResource( $assignment );
        }

        public function destroy(Request $request) : JsonResponse
        {
            foreach ( (array) $request->ids as $id ) {
                Assignment::find( $id )?->delete();
            }

            return response()->json();
        }
    }
