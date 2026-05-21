<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'branch_module_feature' , function (Blueprint $table) {
                $table->bigInteger( 'branch_id' );
                $table->foreignId( 'module_feature_id' )->constrained( 'module_features' )->onDelete( 'cascade' );
                $table->boolean( 'enabled' )->default( FALSE );
                $table->primary( [ 'branch_id' , 'module_feature_id' ] );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'branch_module_feature' );
        }
    };