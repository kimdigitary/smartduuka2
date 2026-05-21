<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'system_modules' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->string( 'description' );
                $table->unsignedInteger( 'price' )->default( 0);
                $table->string( 'icon' );
                $table->foreignId( 'module_category_id' )
                      ->constrained( 'module_categories' )
                      ->onDelete( 'cascade' );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'system_modules' );
        }
    };