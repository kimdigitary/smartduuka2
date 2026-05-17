<?php

    use App\Enums\Status;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'tenant_branches' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->string( 'email' )->nullable()->unique();
                $table->string( 'tenant_id' );
                $table->foreign( 'tenant_id' )->references( 'id' )->on( 'tenants' )->cascadeOnDelete();
                $table->string( 'website' )->nullable()->unique();
                $table->string( 'zip_code' )->nullable();
                $table->unsignedSmallInteger( 'country' )->nullable();
                $table->unsignedSmallInteger( 'city' )->nullable();
                $table->unsignedSmallInteger( 'state' )->nullable();
                $table->string( 'address' )->nullable();
                $table->string( 'phone' )->nullable()->unique();
                $table->string( 'phone2' )->nullable()->unique();
                $table->string( 'code' )->nullable();
                $table->boolean( 'can_delete' )->default( TRUE );
                $table->unsignedTinyInteger( 'status' )->default( Status::ACTIVE );
                $table->timestamps();

                $table->unique( [ 'tenant_id' , 'name' ] );
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'tenant_branches' );
        }
    };
