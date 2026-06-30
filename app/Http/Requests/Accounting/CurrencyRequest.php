<?php

    namespace App\Http\Requests\Accounting;

    use Illuminate\Foundation\Http\FormRequest;

    class CurrencyRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'name'         => [ 'required', 'string', 'max:300' ],
                'currencyCode' => [ 'required', 'string', 'size:3' ],
            ];
        }
    }
