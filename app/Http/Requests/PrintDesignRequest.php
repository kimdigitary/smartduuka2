<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class PrintDesignRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'            => [ 'required' ] ,
                'style'           => [ 'required' , 'integer' ] ,
                'description'     => [ 'required' ] ,
                'recommendations' => [ 'required' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
