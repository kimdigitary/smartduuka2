<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'template_types' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->string( 'description' );
                $table->string( 'icon' );
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'template_types' );
        }
    };
