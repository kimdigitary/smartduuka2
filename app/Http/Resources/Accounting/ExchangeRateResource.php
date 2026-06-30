<?php

    namespace App\Http\Resources\Accounting;

    use IFRS\Models\ExchangeRate;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin ExchangeRate */
    class ExchangeRateResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id,
                'currencyId' => $this->currency_id,
                'rate'       => (float) $this->rate,
                'validFrom'  => optional( $this->valid_from )->toDateString(),
                'validTo'    => optional( $this->valid_to )->toDateString(),
            ];
        }
    }
