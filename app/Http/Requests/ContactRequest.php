<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class ContactRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'first_name' => [ 'required' , 'string' , 'max:100' ] ,
                'last_name'  => [ 'required' , 'string' , 'max:100' ] ,
                'email'      => [ 'required' , 'email' , 'max:190' ] ,
                'phone'      => [ 'required' , 'string' , 'max:30' ] ,
                'company'    => [ 'nullable' , 'string' , 'max:190' ] ,
                'subject'    => [ 'nullable' , 'string' , 'max:50' ] ,
                'message'    => [ 'required' , 'string' , 'min:10' , 'max:5000' ] ,
                // Honeypot — must stay empty. Bots usually fill every field.
                'website'    => [ 'nullable' , 'prohibited' ] ,
            ];
        }
    }
