<?php

    namespace App\Http\Requests\Accounting;

    use Illuminate\Foundation\Http\FormRequest;

    class AssignmentRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'transactionId' => [ 'required', 'integer', 'exists:ifrs_transactions,id' ],
                'clearedId'     => [ 'required', 'integer', 'exists:ifrs_transactions,id' ],
                'amount'        => [ 'required', 'numeric', 'min:0' ],
                'date'          => [ 'nullable', 'date' ],
            ];
        }
    }
