<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserDataRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user(Request $request)
    {
        try {
            $user = $request->user();
//                $centralUser = CentralUser::where(
//                    $user->getGlobalIdentifierKeyName() ,
//                    $user->getGlobalIdentifierKey()
//                )->first();

            $user->setAttribute('has_open_register', $user->registers()->whereNull('closed_at')->latest()->exists());
            $permissions = $user->getAllPermissions();
            $user->unsetRelation('permissions');
            $user->setAttribute('permissions', $permissions);
//                $user->setAttribute( 'tenants' , TenantResource::collection( $centralUser->tenants ) );

            return $user;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 422);
        }
    }

    public function updateUserData(UpdateUserDataRequest $request): UserResource
    {
        $data = $request->validated();
        $user = $request->userToUpdate();

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        return new UserResource($user->refresh());
    }

    public function centralUser(Request $request)
    {
        try {
            return $request->user();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 422);
        }
    }

}
