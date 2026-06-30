<?php

    namespace App\Http\Requests\Accounting;

    use Illuminate\Foundation\Http\FormRequest;

    class BudgetRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        protected function prepareForValidation() : void
        {
            // lines arrives as a JSON string (array serialized by the frontend).
            $lines = $this->input( 'lines' );
            if ( is_string( $lines ) ) {
                $lines = json_decode( $lines, TRUE ) ?: [];
            }

            $this->merge( [ 'lines' => is_array( $lines ) ? $lines : [] ] );
        }

        public function rules() : array
        {
            return [
                'name'              => [ 'required', 'string', 'max:255' ],
                'periodId'          => [ 'required', 'integer', 'exists:ifrs_reporting_periods,id' ],
                'lines'             => [ 'required', 'array', 'min:1' ],
                'lines.*.accountId' => [ 'required', 'integer', 'exists:ifrs_accounts,id' ],
                'lines.*.amount'    => [ 'required', 'numeric' ],
            ];
        }
    }
