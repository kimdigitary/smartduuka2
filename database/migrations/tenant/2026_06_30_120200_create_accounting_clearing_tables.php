<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    /**
     * Backend tables for the last two accounting extension modules:
     *
     *  - accounting_assignments: matches a clearing transaction (receipt / payment)
     *    against a clearable one (invoice / bill) for the chosen amount — drives the
     *    aging schedule (outstanding = original − assigned).
     *  - accounting_bank_reconciliations: per-bank-account reconciliation state
     *    (statement balance + the set of ledger transactions ticked as cleared).
     *
     * Segregated by IFRS entity_id.
     */
    return new class extends Migration {
        public function up() : void
        {
            if ( ! Schema::hasTable( 'accounting_assignments' ) ) {
                Schema::create( 'accounting_assignments', function (Blueprint $table) {
                    $table->bigIncrements( 'id' );
                    $table->unsignedBigInteger( 'entity_id' )->index();
                    $table->unsignedBigInteger( 'transaction_id' )->index(); // clearing txn
                    $table->unsignedBigInteger( 'cleared_id' )->index();      // cleared txn
                    $table->decimal( 'amount', 18, 4 )->default( 0 );
                    $table->date( 'date' );
                    $table->timestamps();
                } );
            }

            if ( ! Schema::hasTable( 'accounting_bank_reconciliations' ) ) {
                Schema::create( 'accounting_bank_reconciliations', function (Blueprint $table) {
                    $table->bigIncrements( 'id' );
                    $table->unsignedBigInteger( 'entity_id' )->index();
                    $table->unsignedBigInteger( 'account_id' );
                    $table->decimal( 'statement_balance', 18, 4 )->default( 0 );
                    $table->json( 'cleared_txn_ids' )->nullable();
                    $table->date( 'last_reconciled_date' )->nullable();
                    $table->timestamps();
                    $table->unique( [ 'entity_id', 'account_id' ] );
                } );
            }
        }

        public function down() : void
        {
            Schema::dropIfExists( 'accounting_bank_reconciliations' );
            Schema::dropIfExists( 'accounting_assignments' );
        }
    };
