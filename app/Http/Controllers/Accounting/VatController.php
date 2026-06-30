<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\VatRequest;
    use App\Http\Resources\Accounting\VatResource;
    use IFRS\Models\Vat;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    class VatController extends Controller
    {
        public function index() : AnonymousResourceCollection
        {
            return VatResource::collection( Vat::orderBy( 'code' )->get() );
        }

        public function store(VatRequest $request) : VatResource
        {
            return new VatResource( $this->fill( new Vat(), $request->validated() ) );
        }

        public function update(VatRequest $request, int $id) : VatResource
        {
            return new VatResource( $this->fill( Vat::findOrFail( $id ), $request->validated() ) );
        }

        public function destroy(Request $request) : JsonResponse
        {
            foreach ( (array) $request->ids as $id ) {
                Vat::find( $id )?->delete();
            }

            return response()->json();
        }

        private function fill(Vat $vat, array $data) : Vat
        {
            $vat->name       = $data[ 'name' ];
            $vat->code       = $data[ 'code' ];
            $vat->rate       = $data[ 'rate' ];
            $vat->account_id = $data[ 'accountId' ] ?? NULL;
            // Our extension columns — set directly (not in the package's $fillable).
            $vat->tax_type    = $data[ 'taxType' ] ?? 'VAT';
            $vat->is_compound = (bool) ( $data[ 'isCompound' ] ?? FALSE );
            $vat->save();

            return $vat;
        }
    }
