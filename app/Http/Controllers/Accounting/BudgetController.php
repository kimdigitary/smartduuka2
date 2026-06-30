<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\BudgetRequest;
    use App\Http\Resources\Accounting\BudgetResource;
    use App\Models\Accounting\Budget;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    class BudgetController extends Controller
    {
        public function index() : AnonymousResourceCollection
        {
            return BudgetResource::collection( Budget::orderBy( 'name' )->get() );
        }

        public function store(BudgetRequest $request) : BudgetResource
        {
            return new BudgetResource( $this->fill( new Budget(), $request->validated() ) );
        }

        public function update(BudgetRequest $request, int $id) : BudgetResource
        {
            return new BudgetResource( $this->fill( Budget::findOrFail( $id ), $request->validated() ) );
        }

        public function destroy(Request $request) : JsonResponse
        {
            foreach ( (array) $request->ids as $id ) {
                Budget::find( $id )?->delete();
            }

            return response()->json();
        }

        private function fill(Budget $budget, array $data) : Budget
        {
            $budget->name      = $data[ 'name' ];
            $budget->period_id = $data[ 'periodId' ];
            $budget->lines     = array_map( static fn ($l) => [
                'accountId' => (int) $l[ 'accountId' ],
                'amount'    => (float) $l[ 'amount' ],
            ], $data[ 'lines' ] );
            $budget->save();

            return $budget;
        }
    }
