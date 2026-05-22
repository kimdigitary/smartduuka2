<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'tenant_personal_access_tokens' , function (Blueprint $table) {
                $table->increments( 'id' );
                $table->string( 'tenant_id' );
                $table->uuid( 'global_token_id' );

                $table->unique( [ 'tenant_id' , 'global_token_id' ] );

                $table->foreign( 'tenant_id' )
                      ->references( 'id' )
                      ->on( 'tenants' )
                      ->onUpdate( 'cascade' )
                      ->onDelete( 'cascade' );

                $table->foreign( 'global_token_id' )
                      ->references( 'global_id' )
                      ->on( 'personal_access_tokens' )
                      ->onUpdate( 'cascade' )
                      ->onDelete( 'cascade' );

            } );
        }


        public function down() : void
        {
            Schema::dropIfExists( 'tenant_personal_access_tokens' );
        }
    };
