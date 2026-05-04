<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'print_designs' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->unsignedTinyInteger( 'style' );
                $table->string( 'description' );
                $table->string( 'recommendations' );
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'print_designs' );
        }
    };
