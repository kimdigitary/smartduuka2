<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserDataRequest extends FormRequest
{
    private ?User $userToUpdate = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [ 'required' , 'string' , 'max:190' , Rule::exists( 'users' , 'id' ) ] ,
            'name'    => [ 'required' , 'string' , 'max:190' ] ,
            'email'   => [
                'required' ,
                'email' ,
                'max:190' ,
                Rule::unique( 'users' , 'email' )->ignore( $this->targetUser()?->id ) ,
            ] ,
            'phone'   => [
                'required' ,
                'string' ,
                'max:20' ,
                Rule::unique( 'users' , 'phone' )->ignore( $this->targetUser()?->id ) ,
            ] ,
        ];
    }

    public function userToUpdate(): User
    {
        return $this->targetUser() ?? User::findOrFail( $this->input( 'user_id' ) );
    }

    private function targetUser(): ?User
    {
        if ( $this->userToUpdate || ! $this->filled( 'user_id' ) ) {
            return $this->userToUpdate;
        }

        $this->userToUpdate = User::find( $this->input( 'user_id' ) );

        return $this->userToUpdate;
    }
}
