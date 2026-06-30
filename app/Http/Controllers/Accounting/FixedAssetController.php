<?php

    namespace App\Http\Controllers\Accounting;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Accounting\FixedAssetRequest;
    use App\Http\Resources\Accounting\FixedAssetResource;
    use App\Models\Accounting\FixedAsset;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    class FixedAssetController extends Controller
    {
        public function index() : AnonymousResourceCollection
        {
            return FixedAssetResource::collection( FixedAsset::orderBy( 'acquisition_date' )->get() );
        }

        public function store(FixedAssetRequest $request) : FixedAssetResource
        {
            return new FixedAssetResource( $this->fill( new FixedAsset(), $request->validated() ) );
        }

        public function update(FixedAssetRequest $request, int $id) : FixedAssetResource
        {
            return new FixedAssetResource( $this->fill( FixedAsset::findOrFail( $id ), $request->validated() ) );
        }

        public function destroy(Request $request) : JsonResponse
        {
            foreach ( (array) $request->ids as $id ) {
                FixedAsset::find( $id )?->delete();
            }

            return response()->json();
        }

        private function fill(FixedAsset $asset, array $data) : FixedAsset
        {
            $asset->name                     = $data[ 'name' ];
            $asset->code                     = $data[ 'code' ] ?? NULL;
            $asset->asset_account_id         = $data[ 'assetAccountId' ];
            $asset->cost                     = $data[ 'cost' ];
            $asset->salvage_value            = $data[ 'salvageValue' ] ?? 0;
            $asset->acquisition_date         = $data[ 'acquisitionDate' ];
            $asset->useful_life_years        = $data[ 'usefulLifeYears' ];
            $asset->method                   = $data[ 'method' ];
            $asset->accumulated_depreciation = $data[ 'accumulatedDepreciation' ] ?? 0;
            $asset->status                   = $data[ 'status' ] ?? 'ACTIVE';
            $asset->branch_id                = $data[ 'branchId' ] ?? NULL;
            $asset->disposal_date            = $data[ 'disposalDate' ] ?? NULL;
            $asset->disposal_proceeds        = $data[ 'disposalProceeds' ] ?? NULL;
            $asset->save();

            return $asset;
        }
    }
