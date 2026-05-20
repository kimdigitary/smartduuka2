<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class PaymentTransactionRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'transaction_id'        => [ 'required' ] ,
                'status'                => [ 'required' , 'integer' ] ,
                'vendor_transaction_id' => [ 'required' ] ,
                'payment_type'          => [ 'required' , 'integer' ] ,
                'amount'                => [ 'required' ] ,
                'phone'                 => [ 'required' ] ,
                'card'                  => [ 'required' ] ,
                'status_message'        => [ 'required' ] ,
                'currency'              => [ 'required' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
