<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\LoanRequest;
    use App\Http\Resources\Accounting\LoanResource;
    use App\Models\Accounting\Loan;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    class LoanController extends Controller
    {
        public function index() : AnonymousResourceCollection
        {
            return LoanResource::collection( Loan::orderBy( 'start_date' )->get() );
        }

        public function store(LoanRequest $request) : LoanResource
        {
            return new LoanResource( $this->fill( new Loan(), $request->validated() ) );
        }

        public function update(LoanRequest $request, int $id) : LoanResource
        {
            return new LoanResource( $this->fill( Loan::findOrFail( $id ), $request->validated() ) );
        }

        public function destroy(Request $request) : JsonResponse
        {
            foreach ( (array) $request->ids as $id ) {
                Loan::find( $id )?->delete();
            }

            return response()->json();
        }

        private function fill(Loan $loan, array $data) : Loan
        {
            $loan->name                  = $data[ 'name' ];
            $loan->lender                = $data[ 'lender' ] ?? NULL;
            $loan->reference             = $data[ 'reference' ] ?? NULL;
            $loan->principal             = $data[ 'principal' ];
            $loan->interest_rate         = $data[ 'interestRate' ] ?? 0;
            $loan->method                = $data[ 'method' ];
            $loan->start_date            = $data[ 'startDate' ];
            $loan->term_months           = $data[ 'termMonths' ];
            $loan->frequency             = $data[ 'frequency' ];
            $loan->liability_account_id  = $data[ 'liabilityAccountId' ];
            $loan->outstanding_principal = $data[ 'outstandingPrincipal' ] ?? $data[ 'principal' ];
            $loan->status                = $data[ 'status' ] ?? 'ACTIVE';
            $loan->branch_id             = $data[ 'branchId' ] ?? NULL;
            $loan->save();

            return $loan;
        }
    }
