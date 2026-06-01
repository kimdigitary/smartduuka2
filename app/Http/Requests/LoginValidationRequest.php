<?php

    namespace App\Http\Requests;

    use Laravel\Fortify\Http\Requests\LoginRequest;

    class LoginValidationRequest extends LoginRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            if ( $this->filled( 'pin' ) ) {
                return [
                    'pin' => 'required|string|size:5' ,
                ];
            }

            $loginField = filter_var( $this->email , FILTER_VALIDATE_EMAIL ) ? 'email' : 'phone';

            return [
                $loginField => 'required|string' ,
                'password'  => 'required|string' ,
            ];
        }
    }
