<?php

    namespace App\Http\Resources\Accounting;

    use App\Models\Accounting\FixedAsset;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin FixedAsset */
    class FixedAssetResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                      => $this->id,
                'name'                    => $this->name,
                'code'                    => $this->code ?? '',
                'assetAccountId'          => $this->asset_account_id,
                'cost'                    => (float) $this->cost,
                'salvageValue'            => (float) $this->salvage_value,
                'acquisitionDate'         => optional( $this->acquisition_date )->toDateString(),
                'usefulLifeYears'         => (int) $this->useful_life_years,
                'method'                  => $this->method,
                'accumulatedDepreciation' => (float) $this->accumulated_depreciation,
                'status'                  => $this->status,
                'branchId'                => $this->branch_id,
                'disposalDate'            => optional( $this->disposal_date )->toDateString(),
                'disposalProceeds'        => $this->disposal_proceeds !== NULL ? (float) $this->disposal_proceeds : NULL,
            ];
        }
    }
