<?php

    namespace Database\Seeders;

    use App\Enums\Status;
    use App\Models\BranchModule;
    use App\Models\BranchModuleFeature;
    use App\Models\Tenant;
    use App\Models\TenantBranch;
    use App\Models\TenantSubscription;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Smartisan\Settings\Facades\Settings;

    class TenantBranchSeeder extends Seeder
    {
        public function run() : void
        {
            Tenant::all()->runForEach( function (Tenant $tenant) {
                BranchModule::truncate();
                BranchModuleFeature::truncate();
                $branch = centralContext( function () use ($tenant) {
                    $branch = TenantBranch::updateOrCreate( [ 'name' => 'Main Branch' , 'tenant_id' => $tenant->id ] , [
                        'can_delete' => FALSE ,
                        'status'     => Status::ACTIVE ,
                    ] );
                    $branch->update( [ 'code' => recordId( 'BR' , $branch , 3 ) ] );
                    return $branch;
                } );

                $company = Settings::group( 'company' )->all();

                $excludedTables = [
                    'branches' ,
                    'migrations' ,
                    'failed_jobs' ,
                    'password_resets' ,
                    'password_reset_tokens' ,
                    'personal_access_tokens' ,
                    'sessions' ,
                    'cache' ,
                    'cache_locks' ,
                    'jobs' ,
                    'job_batches' ,
                    'tenant_subscriptions' ,
                ];

                // Fetch tables exclusively for PostgreSQL
                $tables = array_map(
                    fn($table) => $table->tablename ,
                    DB::select( "SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'" )
                );

                foreach ( $tables as $table ) {
                    if ( in_array( $table , $excludedTables ) ) {
                        continue;
                    }

                    if ( ! Schema::hasColumn( $table , 'branch_id' ) ) {
                        Schema::table( $table , function (Blueprint $tableBlueprint) {
                            $tableBlueprint->unsignedBigInteger( 'branch_id' )->nullable();
                        } );
                    }
                    if ( $tenant->id == 'demoshop' ) {
                        DB::table( $table )->update( [ 'branch_id' => 1 ] );
                    }
                    else {
                        DB::table( $table )->update( [ 'branch_id' => $branch->id ] );
                    }
                }

                $seeder = new SystemModuleSeeder();
                $seeder->run( $branch->id );

                $expiry_date = match ( $tenant->id ) {
                    'glowcitybeauty' , 'ajmalcollections' , 'oaklandpeakltd' , 'timzclassic' , 'jibinicreamaries' => '2026-06-15 23:59:59' ,
                    'techpulsespares' , 'zakayoproduce' , 'digivolvetech' , 'demoshop'                            => '2027-03-15 23:59:59' ,
                    default                                                                                       => now()->addMonth()
                };

                centralContext( fn() => TenantSubscription::updateOrCreate(
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
                    ]
                ) );
            } );
        }
    }
