<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class PaginateRequest extends FormRequest
    {

        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'per_page' => [ 'numeric' , 'min:1' , 'max:1000' ] ,
//                'page'     => [ 'numeric' , 'min:1' , 'max:1000' ]
            ];
        }
    }
