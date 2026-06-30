<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\DeferralRequest;
    use App\Http\Resources\Accounting\DeferralResource;
    use App\Models\Accounting\Deferral;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    class DeferralController extends Controller
    {
        public function index() : AnonymousResourceCollection
        {
            return DeferralResource::collection( Deferral::orderBy( 'start_date' )->get() );
        }

        public function store(DeferralRequest $request) : DeferralResource
        {
            return new DeferralResource( $this->fill( new Deferral(), $request->validated() ) );
        }

        public function update(DeferralRequest $request, int $id) : DeferralResource
        {
            return new DeferralResource( $this->fill( Deferral::findOrFail( $id ), $request->validated() ) );
        }

        public function destroy(Request $request) : JsonResponse
        {
            foreach ( (array) $request->ids as $id ) {
                Deferral::find( $id )?->delete();
            }

            return response()->json();
        }

        private function fill(Deferral $deferral, array $data) : Deferral
        {
            $deferral->name               = $data[ 'name' ];
            $deferral->kind               = $data[ 'kind' ];
            $deferral->total_amount       = $data[ 'totalAmount' ];
            $deferral->expense_account_id = $data[ 'expenseAccountId' ];
            $deferral->balance_account_id = $data[ 'balanceAccountId' ];
            $deferral->start_date         = $data[ 'startDate' ];
            $deferral->months             = $data[ 'months' ];
            $deferral->recognized_amount  = $data[ 'recognizedAmount' ] ?? 0;
            $deferral->branch_id          = $data[ 'branchId' ] ?? NULL;
            $deferral->status             = $data[ 'status' ] ?? 'ACTIVE';
            $deferral->save();

            return $deferral;
        }
    }
