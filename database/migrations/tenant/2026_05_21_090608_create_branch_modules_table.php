<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'branch_modules' , function (Blueprint $table) {
                $table->bigInteger( 'branch_id' );
                $table->foreignId( 'system_module_id' )->constrained( 'system_modules' )->onDelete( 'cascade' );
                $table->boolean( 'enabled' )->default( FALSE );
                $table->primary( [ 'branch_id' , 'system_module_id' ] );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'branch_modules' );
        }
    };
