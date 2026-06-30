<?php

    namespace App\Http\Requests\Accounting;

    use Illuminate\Foundation\Http\FormRequest;

    class VatRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        protected function prepareForValidation() : void
        {
            $this->merge( [
                'isCompound' => filter_var( $this->isCompound, FILTER_VALIDATE_BOOLEAN ),
            ] );
        }

        public function rules() : array
        {
            return [
                'name'       => [ 'required', 'string', 'max:300' ],
                'code'       => [ 'required', 'string', 'max:50' ],
                'rate'       => [ 'required', 'numeric', 'min:0' ],
                'accountId'  => [ 'nullable', 'integer', 'exists:ifrs_accounts,id' ],
                'taxType'    => [ 'nullable', 'in:VAT,WITHHOLDING' ],
                'isCompound' => [ 'boolean' ],
            ];
        }
    }
