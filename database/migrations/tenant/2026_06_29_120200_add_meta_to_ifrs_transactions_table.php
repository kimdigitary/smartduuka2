<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    /**
     * App extensions on IFRS transactions:
     *  - branch_id: reporting dimension (which branch/location the entry belongs to)
     *  - source:    where the entry originated (pos, sale, purchase, expense, manual, import)
     */
    return new class extends Migration {
        private function table() : string
        {
            return config( 'ifrs.table_prefix' ) . 'transactions';
        }

        public function up() : void
        {
            Schema::table( $this->table(), function (Blueprint $table) {
                if ( ! Schema::hasColumn( $this->table(), 'branch_id' ) ) {
                    $table->string( 'branch_id' )->nullable()->index();
                }
                if ( ! Schema::hasColumn( $this->table(), 'source' ) ) {
                    $table->string( 'source' )->nullable()->index();
                }
            } );
        }

        public function down() : void
        {
            Schema::table( $this->table(), function (Blueprint $table) {
                $table->dropColumn( [ 'branch_id', 'source' ] );
            } );
        }
    };
