<?php

    namespace App\Http\Requests\Accounting;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class LoanRequest extends FormRequest
    {
        private const METHODS     = [ 'REDUCING_BALANCE', 'FLAT' ];
        private const FREQUENCIES = [ 'MONTHLY', 'QUARTERLY' ];
        private const STATUSES    = [ 'ACTIVE', 'SETTLED' ];

        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'name'                 => [ 'required', 'string', 'max:255' ],
                'lender'               => [ 'nullable', 'string', 'max:255' ],
                'reference'            => [ 'nullable', 'string', 'max:255' ],
                'principal'            => [ 'required', 'numeric' ],
                'interestRate'         => [ 'nullable', 'numeric' ],
                'method'               => [ 'required', Rule::in( self::METHODS ) ],
                'startDate'            => [ 'required', 'date' ],
                'termMonths'           => [ 'required', 'integer', 'min:1' ],
                'frequency'            => [ 'required', Rule::in( self::FREQUENCIES ) ],
                'liabilityAccountId'   => [ 'required', 'integer', 'exists:ifrs_accounts,id' ],
                'outstandingPrincipal' => [ 'nullable', 'numeric' ],
                'status'               => [ 'nullable', Rule::in( self::STATUSES ) ],
                'branchId'             => [ 'nullable' ],
            ];
        }
    }
