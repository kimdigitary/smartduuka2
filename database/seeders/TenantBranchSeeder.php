<?php

    namespace Database\Seeders;

    use App\Enums\Status;
    use App\Models\TenantBranch;
    use Illuminate\Database\Seeder;

    class TenantBranchSeeder extends Seeder
    {
        public function run() : void
        {
            $tenant = tenant();
            centralContext( function () use ($tenant) {
                $branch = TenantBranch::firstOrCreate( [ 'name' => 'Main Branch' , 'tenant_id' => $tenant->id ] , [
                    'can_delete' => FALSE ,
                    'status'     => Status::ACTIVE
                ] );
                $branch->update( [ 'code' => recordId( 'BR' , $branch , 3 ) ] );
            } );
        }
    }
