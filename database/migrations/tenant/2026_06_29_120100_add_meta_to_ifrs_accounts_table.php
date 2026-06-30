<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    /**
     * App extensions on IFRS accounts:
     *  - is_active:     soft enable/disable without deleting
     *  - is_petty_cash: marks a BANK account as a petty-cash float
     *  - party_type / party_id: links a RECEIVABLE/PAYABLE sub-account to an
     *    operational customer/supplier (per-party ledgers).
     */
    return new class extends Migration {
        private function table() : string
        {
            return config( 'ifrs.table_prefix' ) . 'accounts';
        }

        public function up() : void
        {
            Schema::table( $this->table(), function (Blueprint $table) {
                if ( ! Schema::hasColumn( $this->table(), 'is_active' ) ) {
                    $table->boolean( 'is_active' )->default( TRUE );
                }
                if ( ! Schema::hasColumn( $this->table(), 'is_petty_cash' ) ) {
                    $table->boolean( 'is_petty_cash' )->default( FALSE );
                }
                if ( ! Schema::hasColumn( $this->table(), 'party_type' ) ) {
                    $table->string( 'party_type' )->nullable();
                }
                if ( ! Schema::hasColumn( $this->table(), 'party_id' ) ) {
                    $table->string( 'party_id' )->nullable();
                }
            } );
        }

        public function down() : void
        {
            Schema::table( $this->table(), function (Blueprint $table) {
                $table->dropColumn( [ 'is_active', 'is_petty_cash', 'party_type', 'party_id' ] );
            } );
        }
    };
