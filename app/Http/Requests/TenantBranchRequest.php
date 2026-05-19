<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class TenantBranchRequest extends FormRequest
    {
        protected function prepareForValidation()
        {
            $this->merge( [
                'share_customers'   => filter_var( $this->share_customers , FILTER_VALIDATE_BOOLEAN ) ,
                'share_wallets'     => filter_var( $this->share_wallets , FILTER_VALIDATE_BOOLEAN ) ,
                'share_loyalty'     => filter_var( $this->share_loyalty , FILTER_VALIDATE_BOOLEAN ) ,
                'share_accounting'  => filter_var( $this->share_accounting , FILTER_VALIDATE_BOOLEAN ) ,
                'share_reports'     => filter_var( $this->share_reports , FILTER_VALIDATE_BOOLEAN ) ,
                'share_procurement' => filter_var( $this->share_procurement , FILTER_VALIDATE_BOOLEAN ) ,
            ] );
        }

        public function rules() : array
        {
            return [
                'name'              => [
                    'required' ,
                    Rule::unique( 'tenant_branches' , 'name' )
                        ->where( 'tenant_id' , $this->input( 'tenant_id' ) )
                        ->ignore( $this->route( 'tenant_branch' ) )
                ] ,
                'email'             => [ 'required' , 'email' , 'max:254' ] ,
                'tenant_id'         => [ 'required' , 'exists:tenants,id' ] ,
                'website'           => [ 'required' ] ,
                'zip_code'          => [ 'required' ] ,
                'country'           => [ 'required' , 'integer' ] ,
//                'city'      => [ 'required' , 'integer' ] ,
                'state'             => [ 'required' , 'integer' ] ,
                'address'           => [ 'required' ] ,
                'phone'             => [ 'required' ] ,
                'phone2'            => [ 'required' ] ,
//                'code'      => [ 'required' ] ,
                'status'            => [ 'required' , 'integer' ] ,
                'share_customers'   => [ 'required' , 'boolean' ] ,
                'share_wallets'     => [ 'required' , 'boolean' ] ,
                'share_loyalty'     => [ 'required' , 'boolean' ] ,
                'share_accounting'  => [ 'required' , 'boolean' ] ,
                'share_reports'     => [ 'required' , 'boolean' ] ,
                'share_procurement' => [ 'required' , 'boolean' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }