<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class TemplateTypeRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'        => [ 'required' ] ,
                'description' => [ 'required' ] ,
                'icon'        => [ 'required' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
