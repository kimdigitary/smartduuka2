<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'tenant_branches' , function (Blueprint $table) {
                $table->boolean( 'share_customers' )->default( TRUE );
                $table->boolean( 'share_wallets' )->default( TRUE );
                $table->boolean( 'share_loyalty' )->default( TRUE );
                $table->boolean( 'share_accounting' )->default( TRUE );
                $table->boolean( 'share_reports' )->default( TRUE );
                $table->boolean( 'share_procurement' )->default( TRUE );
            } );
        }

        public function down() : void
        {
            Schema::table( 'tenant_branches' , function (Blueprint $table) {
                $table->dropColumn( [
                    'share_customers' ,
                    'share_wallets' ,
                    'share_loyalty' ,
                    'share_accounting' ,
                    'share_reports' ,
                    'share_procurement' ,
                ] );
            } );
        }
    };
