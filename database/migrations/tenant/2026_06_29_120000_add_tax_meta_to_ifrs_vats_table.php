<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    /**
     * Our app extends IFRS VAT with two extra knobs the frontend needs:
     *  - tax_type:   VAT vs WITHHOLDING
     *  - is_compound: tax charged on top of other taxes
     */
    return new class extends Migration {
        private function table() : string
        {
            return config( 'ifrs.table_prefix' ) . 'vats';
        }

        public function up() : void
        {
            Schema::table( $this->table(), function (Blueprint $table) {
                if ( ! Schema::hasColumn( $this->table(), 'tax_type' ) ) {
                    $table->string( 'tax_type' )->default( 'VAT' )->after( 'rate' );
                }
                if ( ! Schema::hasColumn( $this->table(), 'is_compound' ) ) {
                    $table->boolean( 'is_compound' )->default( FALSE )->after( 'tax_type' );
                }
            } );
        }

        public function down() : void
        {
            Schema::table( $this->table(), function (Blueprint $table) {
                $table->dropColumn( [ 'tax_type', 'is_compound' ] );
            } );
        }
    };
