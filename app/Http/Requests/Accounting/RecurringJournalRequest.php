<?php

    namespace App\Http\Requests\Accounting;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class RecurringJournalRequest extends FormRequest
    {
        private const FREQUENCIES = [ 'WEEKLY', 'MONTHLY', 'QUARTERLY', 'YEARLY' ];

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

            $this->merge( [
                'lines'  => is_array( $lines ) ? $lines : [],
                'active' => filter_var( $this->input( 'active', TRUE ), FILTER_VALIDATE_BOOLEAN ),
            ] );
        }

        public function rules() : array
        {
            return [
                'name'              => [ 'required', 'string', 'max:255' ],
                'frequency'         => [ 'required', Rule::in( self::FREQUENCIES ) ],
                'startDate'         => [ 'required', 'date' ],
                'nextRunDate'       => [ 'required', 'date' ],
                'endDate'           => [ 'nullable', 'date' ],
                'narration'         => [ 'nullable', 'string', 'max:1000' ],
                'reference'         => [ 'nullable', 'string', 'max:255' ],
                'branchId'          => [ 'nullable' ],
                'active'            => [ 'boolean' ],
                'lastRunDate'       => [ 'nullable', 'date' ],
                'lines'             => [ 'required', 'array', 'min:1' ],
                'lines.*.accountId' => [ 'required', 'integer', 'exists:ifrs_accounts,id' ],
                'lines.*.amount'    => [ 'required', 'numeric' ],
                'lines.*.credited'  => [ 'boolean' ],
            ];
        }
    }
