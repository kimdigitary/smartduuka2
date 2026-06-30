<?php

    namespace App\Http\Requests\Accounting;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class DeferralRequest extends FormRequest
    {
        private const KINDS    = [ 'ACCRUAL', 'PREPAYMENT' ];
        private const STATUSES = [ 'ACTIVE', 'COMPLETED' ];

        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'name'             => [ 'required', 'string', 'max:255' ],
                'kind'             => [ 'required', Rule::in( self::KINDS ) ],
                'totalAmount'      => [ 'required', 'numeric' ],
                'expenseAccountId' => [ 'required', 'integer', 'exists:ifrs_accounts,id' ],
                'balanceAccountId' => [ 'required', 'integer', 'exists:ifrs_accounts,id' ],
                'startDate'        => [ 'required', 'date' ],
                'months'           => [ 'required', 'integer', 'min:1' ],
                'recognizedAmount' => [ 'nullable', 'numeric' ],
                'branchId'         => [ 'nullable' ],
                'status'           => [ 'nullable', Rule::in( self::STATUSES ) ],
            ];
        }
    }
