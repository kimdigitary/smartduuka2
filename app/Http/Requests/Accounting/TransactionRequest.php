<?php

    namespace App\Http\Requests\Accounting;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class TransactionRequest extends FormRequest
    {
        private const TYPES = [ 'CS', 'IN', 'CN', 'RC', 'CP', 'BL', 'DN', 'PY', 'CE', 'JN' ];

        public function authorize() : bool
        {
            return TRUE;
        }

        protected function prepareForValidation() : void
        {
            // lineItems arrives as a JSON string (array serialized by the frontend).
            $items = $this->input( 'lineItems' );
            if ( is_string( $items ) ) {
                $items = json_decode( $items, TRUE ) ?: [];
            }

            $this->merge( [
                'lineItems' => is_array( $items ) ? $items : [],
                'credited'  => filter_var( $this->credited, FILTER_VALIDATE_BOOLEAN ),
                'compound'  => filter_var( $this->compound, FILTER_VALIDATE_BOOLEAN ),
            ] );
        }

        public function rules() : array
        {
            return [
                'transactionType'      => [ 'nullable', Rule::in( self::TYPES ) ],
                'date'                 => [ 'required', 'date' ],
                'narration'            => [ 'nullable', 'string', 'max:1000' ],
                'accountId'            => [ 'required', 'integer', 'exists:ifrs_accounts,id' ],
                'currencyId'           => [ 'nullable', 'integer', 'exists:ifrs_currencies,id' ],
                'reference'            => [ 'nullable', 'string', 'max:255' ],
                'credited'             => [ 'boolean' ],
                'compound'             => [ 'boolean' ],
                'branchId'             => [ 'nullable' ],
                'source'               => [ 'nullable', 'string', 'max:50' ],
                'lineItems'            => [ 'required', 'array', 'min:1' ],
                'lineItems.*.accountId' => [ 'required', 'integer', 'exists:ifrs_accounts,id' ],
                'lineItems.*.amount'    => [ 'required', 'numeric' ],
                'lineItems.*.credited'  => [ 'boolean' ],
                'lineItems.*.quantity'  => [ 'nullable', 'numeric' ],
                'lineItems.*.vatAmount' => [ 'nullable', 'numeric' ],
            ];
        }
    }
