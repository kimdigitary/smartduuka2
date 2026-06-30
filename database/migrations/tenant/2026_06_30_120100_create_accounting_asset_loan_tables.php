<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    /**
     * Backend tables for the remaining accounting extension modules:
     *
     *  - accounting_fixed_assets: depreciable assets (cost, salvage, life, method,
     *    accumulated depreciation) with disposal info. Depreciation/disposal post
     *    IFRS journals via the normal transaction endpoint.
     *  - accounting_loans: borrowings (principal, rate, term) with the outstanding
     *    balance; drawdown/repayment post journals.
     *
     * Segregated by IFRS entity_id. Money columns mirror decimal(18,4).
     */
    return new class extends Migration {
        public function up() : void
        {
            if ( ! Schema::hasTable( 'accounting_fixed_assets' ) ) {
                Schema::create( 'accounting_fixed_assets', function (Blueprint $table) {
                    $table->bigIncrements( 'id' );
                    $table->unsignedBigInteger( 'entity_id' )->index();
                    $table->string( 'name' );
                    $table->string( 'code' )->nullable();
                    $table->unsignedBigInteger( 'asset_account_id' );
                    $table->decimal( 'cost', 18, 4 )->default( 0 );
                    $table->decimal( 'salvage_value', 18, 4 )->default( 0 );
                    $table->date( 'acquisition_date' );
                    $table->unsignedInteger( 'useful_life_years' )->default( 1 );
                    $table->string( 'method' )->default( 'STRAIGHT_LINE' );
                    $table->decimal( 'accumulated_depreciation', 18, 4 )->default( 0 );
                    $table->string( 'status' )->default( 'ACTIVE' );
                    $table->string( 'branch_id' )->nullable()->index();
                    $table->date( 'disposal_date' )->nullable();
                    $table->decimal( 'disposal_proceeds', 18, 4 )->nullable();
                    $table->timestamps();
                } );
            }

            if ( ! Schema::hasTable( 'accounting_loans' ) ) {
                Schema::create( 'accounting_loans', function (Blueprint $table) {
                    $table->bigIncrements( 'id' );
                    $table->unsignedBigInteger( 'entity_id' )->index();
                    $table->string( 'name' );
                    $table->string( 'lender' )->nullable();
                    $table->string( 'reference' )->nullable();
                    $table->decimal( 'principal', 18, 4 )->default( 0 );
                    $table->decimal( 'interest_rate', 8, 4 )->default( 0 );
                    $table->string( 'method' )->default( 'REDUCING_BALANCE' );
                    $table->date( 'start_date' );
                    $table->unsignedInteger( 'term_months' )->default( 1 );
                    $table->string( 'frequency' )->default( 'MONTHLY' );
                    $table->unsignedBigInteger( 'liability_account_id' );
                    $table->decimal( 'outstanding_principal', 18, 4 )->default( 0 );
                    $table->string( 'status' )->default( 'ACTIVE' );
                    $table->string( 'branch_id' )->nullable()->index();
                    $table->timestamps();
                } );
            }
        }

        public function down() : void
        {
            Schema::dropIfExists( 'accounting_loans' );
            Schema::dropIfExists( 'accounting_fixed_assets' );
        }
    };
