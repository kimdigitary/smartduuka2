<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'tenant_subscriptions' , function (Blueprint $table) {
                $table->foreignId( 'tenant_branch_id' )->nullable()->constrained();
            } );
        }

        public function down() : void
        {
            Schema::table( 'tenant_subscriptions' , function (Blueprint $table) {
                $table->dropForeign( 'branch_id' );
            } );
        }
    };
