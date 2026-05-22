<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class TenantSubscriptionRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'phone'            => [ 'required' , 'string' ] ,
                'tenant'           => [ 'required' , 'string' ] ,
                'modules'          => [ 'required' , 'string' ] ,
                'amount'           => [ 'required' , 'numeric:' ] ,
                'email'            => [ 'required' , 'email' ] ,
                'branch_id'        => [ 'required' , 'numeric:' ] ,
                'billingCycle'     => [ 'required' , 'numeric:' ] ,
                'subscriptionPlan' => [ 'required' , 'numeric:' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
