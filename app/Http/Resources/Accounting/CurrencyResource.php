<?php

    namespace App\Http\Resources\Accounting;

    use IFRS\Models\Currency;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Facades\Auth;

    /** @mixin Currency */
    class CurrencyResource extends JsonResource
    {
        private const SYMBOLS = [
            'UGX' => 'USh', 'KES' => 'KSh', 'TZS' => 'TSh', 'RWF' => 'FRw',
            'USD' => '$', 'EUR' => '€', 'GBP' => '£',
        ];

        public function toArray(Request $request) : array
        {
            $reportingId = (int) ( Auth::user()?->entity?->currency_id );

            return [
                'id'           => $this->id,
                'name'         => $this->name,
                'currencyCode' => $this->currency_code,
                'symbol'       => self::SYMBOLS[ $this->currency_code ] ?? $this->currency_code,
                'isReporting'  => (int) $this->id === $reportingId,
            ];
        }
    }
