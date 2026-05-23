<?php

    namespace Database\Seeders;

    use App\Enums\Role as EnumRole;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Schema;
    use Spatie\Permission\PermissionRegistrar;

    class RoleTableSeeder extends Seeder
    {
        public function run() : void
        {
            $tableNames = config( 'permission.table_names' );

            if ( ! Schema::hasTable( $tableNames[ 'roles' ] ) ) {
                Artisan::call( 'tenants:migrate' );
                Log::warning( "Skipping RoleTableSeeder: Table {$tableNames['roles']} does not exist." );
            }

            // 1. Check if the roles table already has data.
            if ( DB::table( $tableNames[ 'roles' ] )->count() > 0 ) {
                return;
            }

            // 2. Clear the Spatie cache to ensure we aren't using stale central data
            app()[ PermissionRegistrar::class ]->forgetCachedPermissions();

            $roles = [
                [ 'name' => EnumRole::ADMIN , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
                [ 'name' => EnumRole::CUSTOMER , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
                [ 'name' => EnumRole::MANAGER , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
                [ 'name' => EnumRole::POS_OPERATOR , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
                [ 'name' => EnumRole::STUFF , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
                [ 'name' => EnumRole::DISTRIBUTOR , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
            ];

            // 3. Perform a bulk insert
            DB::table( $tableNames[ 'roles' ] )->insert( $roles );
        }
    }
