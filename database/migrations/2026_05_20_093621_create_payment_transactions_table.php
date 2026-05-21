<?php

    use App\Enums\TransactionPaymentStatus;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'payment_transactions' , function (Blueprint $table) {
                $table->id();
                $table->string( 'transaction_id' )->nullable();
                $table->unsignedTinyInteger( 'status' )->default( TransactionPaymentStatus::PENDING );
                $table->foreignId( 'tenant_branch_id' )->constrained();
                $table->string( 'tenant_id' );
                $table->json( 'data' )->nullable();
                $table->foreign( 'tenant_id' )->references( 'id' )->on( 'tenants' )->onUpdate( 'cascade' )->onDelete( 'cascade' );
                $table->string( 'vendor_transaction_id' )->nullable();
                $table->unsignedTinyInteger( 'payment_type' );
                $table->string( 'payment_type_id' );
                $table->string( 'amount' );
                $table->string( 'phone' )->nullable();
                $table->string( 'card' )->nullable();
                $table->string( 'status_message' )->nullable();
                $table->string( 'currency' )->default( 'UGX' );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'payment_transactions' );
        }
    };
