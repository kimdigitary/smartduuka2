<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    /**
     * Track who/when a reporting period was closed (the frontend shows this).
     */
    return new class extends Migration {
        private function table() : string
        {
            return config( 'ifrs.table_prefix' ) . 'reporting_periods';
        }

        public function up() : void
        {
            Schema::table( $this->table(), function (Blueprint $table) {
                if ( ! Schema::hasColumn( $this->table(), 'closed_at' ) ) {
                    $table->timestamp( 'closed_at' )->nullable();
                }
                if ( ! Schema::hasColumn( $this->table(), 'closed_by' ) ) {
                    $table->string( 'closed_by' )->nullable();
                }
            } );
        }

        public function down() : void
        {
            Schema::table( $this->table(), function (Blueprint $table) {
                $table->dropColumn( [ 'closed_at', 'closed_by' ] );
            } );
        }
    };
