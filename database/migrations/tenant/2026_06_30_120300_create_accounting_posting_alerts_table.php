<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    /**
     * Records operational postings that could not reach the ledger because their
     * reporting period is CLOSED (e.g. an order edited after year-end close). The
     * operational change still happened, so the accountant is alerted to post a
     * manual adjusting entry instead of the change being silently lost.
     */
    return new class extends Migration {
        public function up() : void
        {
            if ( ! Schema::hasTable( 'accounting_posting_alerts' ) ) {
                Schema::create( 'accounting_posting_alerts', function (Blueprint $table) {
                    $table->bigIncrements( 'id' );
                    $table->unsignedBigInteger( 'entity_id' )->index();
                    $table->string( 'source' )->index();
                    $table->string( 'source_id' )->nullable();
                    $table->date( 'posting_date' )->nullable();
                    $table->string( 'narration' )->nullable();
                    $table->string( 'message' )->nullable();
                    $table->timestamp( 'resolved_at' )->nullable();
                    $table->timestamps();
                    $table->unique( [ 'entity_id', 'source', 'source_id' ] );
                } );
            }
        }

        public function down() : void
        {
            Schema::dropIfExists( 'accounting_posting_alerts' );
        }
    };
