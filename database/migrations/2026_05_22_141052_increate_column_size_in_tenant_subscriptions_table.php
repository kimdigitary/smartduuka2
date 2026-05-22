<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {

        public function up() : void
        {
            Schema::table( 'tenant_subscriptions' , function (Blueprint $table) {
//                $table->decimal('setup', 20, 2)->change();
                $table->decimal('amount', 20 )->change();
            } );
        }

        public function down() : void
        {
            Schema::table( 'tenant_subscriptions' , function (Blueprint $table) {
//                $table->decimal('setup')->change();
                $table->decimal('amount')->change();
            } );
        }
    };
