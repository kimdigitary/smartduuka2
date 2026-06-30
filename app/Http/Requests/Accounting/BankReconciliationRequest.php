<?php

    namespace App\Http\Requests\Accounting;

    use Illuminate\Foundation\Http\FormRequest;

    class BankReconciliationRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        protected function prepareForValidation() : void
        {
            // clearedTxnIds arrives as a JSON string (array serialized by the frontend).
            $ids = $this->input( 'clearedTxnIds' );
            if ( is_string( $ids ) ) {
                $ids = json_decode( $ids, TRUE ) ?: [];
            }

            $this->merge( [ 'clearedTxnIds' => is_array( $ids ) ? array_values( $ids ) : [] ] );
        }

        public function rules() : array
        {
            return [
                'accountId'          => [ 'required', 'integer' ],
                'statementBalance'   => [ 'nullable', 'numeric' ],
                'clearedTxnIds'      => [ 'nullable', 'array' ],
                'lastReconciledDate' => [ 'nullable', 'date' ],
            ];
        }
    }
