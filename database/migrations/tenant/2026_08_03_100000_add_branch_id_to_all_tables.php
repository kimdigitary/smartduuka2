<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
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
                'tenants' ,
                'domains' ,
                'subscriptions' ,
                'tenant_subscriptions' ,
                'business_on_boards' ,
                'payment_transactions' ,
                'activity_log' ,
            ];

            // This logic is for PostgreSQL.
            $tables = array_map(
                fn($table) => $table->tablename ,
                DB::select( "SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'" )
            );

            foreach ( $tables as $table ) {
                if ( in_array( $table , $excludedTables , TRUE ) ) {
                    continue;
                }

                if ( ! Schema::hasColumn( $table , 'branch_id' ) ) {
                    Schema::table( $table , function (Blueprint $tableBlueprint) {
                        $tableBlueprint->unsignedBigInteger( 'branch_id' )->default( 1 );
                    } );
                }
            }
        }
    };
