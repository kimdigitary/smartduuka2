<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Requests\PermissionRequest;
    use App\Http\Resources\RoleResource;
    use App\Libraries\AppLibrary;
    use App\Services\PermissionService;
    use Exception;
    use Spatie\Permission\Models\Permission;
    use Spatie\Permission\Models\Role;

    class PermissionController extends AdminController
    {
        private PermissionService $permissionService;

        public function __construct(PermissionService $permissionService)
        {
            parent::__construct();
            $this->permissionService = $permissionService;
        }

        public function index(Role $role)
        {
            try {
                $permissions     = Permission::orderBy( 'id' )->get();
                $rolePermissions = Permission::join(
                    'role_has_permissions' ,
                    'role_has_permissions.permission_id' ,
                    '=' ,
                    'permissions.id'
                )->where( 'role_has_permissions.role_id' , $role->id )->get()->pluck( 'name' , 'id' );
                $permissions     = AppLibrary::permissionWithAccess( $permissions , $rolePermissions );
                $permissions     = AppLibrary::buildPermissionTree( $permissions->toArray() );

                return [
                    'data' => [
                        'role'        => $role ,
                        'permissions' => $permissions ,
                    ]
                ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(PermissionRequest $request , Role $role)
        {
            info( $request->headers );
            try {
                return new RoleResource( $this->permissionService->update( $request , $role ) );
            } catch ( Exception $exception ) {
                info($exception->getMessage());
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
