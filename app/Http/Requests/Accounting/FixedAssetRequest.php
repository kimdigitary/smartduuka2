<?php

    namespace App\Http\Requests\Accounting;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class FixedAssetRequest extends FormRequest
    {
        private const METHODS  = [ 'STRAIGHT_LINE', 'REDUCING_BALANCE' ];
        private const STATUSES = [ 'ACTIVE', 'DISPOSED' ];

        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'name'                    => [ 'required', 'string', 'max:255' ],
                'code'                    => [ 'nullable', 'string', 'max:50' ],
                'assetAccountId'          => [ 'required', 'integer', 'exists:ifrs_accounts,id' ],
                'cost'                    => [ 'required', 'numeric' ],
                'salvageValue'            => [ 'nullable', 'numeric' ],
                'acquisitionDate'         => [ 'required', 'date' ],
                'usefulLifeYears'         => [ 'required', 'integer', 'min:1' ],
                'method'                  => [ 'required', Rule::in( self::METHODS ) ],
                'accumulatedDepreciation' => [ 'nullable', 'numeric' ],
                'status'                  => [ 'nullable', Rule::in( self::STATUSES ) ],
                'branchId'                => [ 'nullable' ],
                'disposalDate'            => [ 'nullable', 'date' ],
                'disposalProceeds'        => [ 'nullable', 'numeric' ],
            ];
        }
    }
