<?php

    namespace App\Http\Resources\Accounting;

    use IFRS\Models\Vat;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Vat */
    class VatResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id,
                'name'       => $this->name,
                'code'       => $this->code,
                'rate'       => (float) $this->rate,
                'accountId'  => $this->account_id,
                'taxType'    => $this->tax_type ?? 'VAT',
                'isCompound' => (bool) $this->is_compound,
            ];
        }
    }
