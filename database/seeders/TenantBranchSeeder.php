<?php

    namespace Database\Seeders;

    use App\Enums\Status;
    use App\Models\TenantBranch;
    use App\Models\TenantSubscription;
    use Illuminate\Database\Seeder;
    use Smartisan\Settings\Facades\Settings;

    class TenantBranchSeeder extends Seeder
    {
        public function run() : void
        {
            $tenant = tenant();
            centralContext( function () use ($tenant) {
                $branch = TenantBranch::updateOrCreate( [ 'name' => 'Main Branch' , 'tenant_id' => $tenant->id ] , [
                    'can_delete' => FALSE ,
                    'status'     => Status::ACTIVE
                ] );
                $branch->update( [ 'code' => recordId( 'BR' , $branch , 3 ) ] );

                $company = tenantContext( fn() => Settings::group( 'company' ) );

                tenantContext( function () use ($branch) {
                    $seeder = new SystemModuleSeeder();
                    $seeder->run( $branch->id );
                } , $tenant->id );

                $expiry_date = match ( $tenant->id ) {
                    'glowcitybeauty' , 'ajmalcollections' , 'oaklandpeakltd' , 'timzclassic' , 'jibinicreamaries' => '2026-06-15 23:59:59' ,
                    'techpulsespares' , 'zakayoproduce' , 'digivolvetech' , 'demoshop'                            => '2027-03-15 23:59:59' ,
                    default                                                                                       => now()->addMonth()

                };

                TenantSubscription::updateOrCreate(
                    [ 'branch_id' => $branch->id , 'tenant_id' => $tenant->id ] ,
                    [
                        'phone'                => $company[ 'company_phone' ] ,
                        'amount'               => 52000 ,
                        'branch_id'            => $branch->id ,
                        'billing_cycle_id'     => 1 ,
                        'tenant_id'            => $tenant->id ,
                        'subscription_plan_id' => 1 ,
                        'status'               => Status::ACTIVE ,
                        'expires_at'           => $expiry_date ,
                    ] );
            } );
        }
    }
