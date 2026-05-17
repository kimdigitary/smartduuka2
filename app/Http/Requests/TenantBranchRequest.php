<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class TenantBranchRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'      => [
                    'required' ,
                    Rule::unique( 'tenant_branches' , 'name' )
                        ->where( 'tenant_id' , $this->input( 'tenant_id' ) )
                        ->ignore( $this->route( 'tenant_branch' ) )
                ] ,
                'email'     => [ 'required' , 'email' , 'max:254' ] ,
                'tenant_id' => [ 'required' , 'exists:tenants,id' ] ,
                'website'   => [ 'required' ] ,
                'zip_code'  => [ 'required' ] ,
                'country'   => [ 'required' , 'integer' ] ,
//                'city'      => [ 'required' , 'integer' ] ,
                'state'     => [ 'required' , 'integer' ] ,
                'address'   => [ 'required' ] ,
                'phone'     => [ 'required' ] ,
                'phone2'    => [ 'required' ] ,
//                'code'      => [ 'required' ] ,
                'status'    => [ 'required' , 'integer' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
