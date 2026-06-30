<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\CurrencyRequest;
    use App\Http\Resources\Accounting\CurrencyResource;
    use IFRS\Models\Currency;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Support\Facades\Auth;

    class CurrencyController extends Controller
    {
        public function index() : AnonymousResourceCollection
        {
            return CurrencyResource::collection( Currency::orderBy( 'currency_code' )->get() );
        }

        public function store(CurrencyRequest $request) : CurrencyResource
        {
            return new CurrencyResource( $this->fill( new Currency(), $request->validated() ) );
        }

        public function update(CurrencyRequest $request, int $id) : CurrencyResource
        {
            return new CurrencyResource( $this->fill( Currency::findOrFail( $id ), $request->validated() ) );
        }

        public function destroy(Request $request) : JsonResponse
        {
            $reportingId = (int) ( Auth::user()?->entity?->currency_id );

            foreach ( (array) $request->ids as $id ) {
                if ( (int) $id === $reportingId ) {
                    continue; // never delete the reporting currency
                }
                Currency::find( $id )?->delete();
            }

            return response()->json();
        }

        private function fill(Currency $currency, array $data) : Currency
        {
            $currency->name          = $data[ 'name' ];
            $currency->currency_code = strtoupper( $data[ 'currencyCode' ] );
            $currency->save();

            return $currency;
        }
    }
