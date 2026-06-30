<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\ExchangeRateRequest;
    use App\Http\Resources\Accounting\ExchangeRateResource;
    use IFRS\Models\ExchangeRate;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    class ExchangeRateController extends Controller
    {
        public function index() : AnonymousResourceCollection
        {
            return ExchangeRateResource::collection( ExchangeRate::latest( 'valid_from' )->get() );
        }

        public function store(ExchangeRateRequest $request) : ExchangeRateResource
        {
            return new ExchangeRateResource( $this->fill( new ExchangeRate(), $request->validated() ) );
        }

        public function update(ExchangeRateRequest $request, int $id) : ExchangeRateResource
        {
            return new ExchangeRateResource( $this->fill( ExchangeRate::findOrFail( $id ), $request->validated() ) );
        }

        public function destroy(Request $request) : JsonResponse
        {
            foreach ( (array) $request->ids as $id ) {
                ExchangeRate::find( $id )?->delete();
            }

            return response()->json();
        }

        private function fill(ExchangeRate $rate, array $data) : ExchangeRate
        {
            $rate->currency_id = $data[ 'currencyId' ];
            $rate->rate        = $data[ 'rate' ];
            $rate->valid_from  = $data[ 'validFrom' ];
            $rate->valid_to    = $data[ 'validTo' ] ?? NULL;
            $rate->save();

            return $rate;
        }
    }
