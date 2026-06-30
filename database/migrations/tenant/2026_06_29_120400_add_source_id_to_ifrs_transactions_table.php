<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    /**
     * source_id links an auto-posted IFRS transaction back to the operational
     * record that produced it (order/expense/wallet id) so posting is idempotent
     * (one ledger entry per source record).
     */
    return new class extends Migration {
        private function table() : string
        {
            return config( 'ifrs.table_prefix' ) . 'transactions';
        }

        public function up() : void
        {
            Schema::table( $this->table(), function (Blueprint $table) {
                if ( ! Schema::hasColumn( $this->table(), 'source_id' ) ) {
                    $table->string( 'source_id' )->nullable()->index();
                }
            } );
        }

        public function down() : void
        {
            Schema::table( $this->table(), function (Blueprint $table) {
                $table->dropColumn( 'source_id' );
            } );
        }
    };
