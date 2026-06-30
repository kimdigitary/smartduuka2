<?php

    namespace App\Http\Requests\Accounting;

    use Illuminate\Foundation\Http\FormRequest;

    class ExchangeRateRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'currencyId' => [ 'required', 'integer', 'exists:ifrs_currencies,id' ],
                'rate'       => [ 'required', 'numeric', 'min:0' ],
                'validFrom'  => [ 'required', 'date' ],
                'validTo'    => [ 'nullable', 'date', 'after_or_equal:validFrom' ],
            ];
        }
    }
