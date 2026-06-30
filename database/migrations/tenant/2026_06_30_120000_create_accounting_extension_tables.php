<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    /**
     * Backend tables for the accounting "extension" modules that the frontend
     * already drives (recurring-entries, accruals/prepayments and budgets) but
     * which the eloquent-ifrs package does not provide:
     *
     *  - accounting_recurring_journals: a repeating balanced journal template
     *    that the UI "runs" to post an IFRS transaction and roll its next-run.
     *  - accounting_deferrals:          an accrual / prepayment recognised in
     *    equal slices across N months (each slice posts a journal).
     *  - accounting_budgets:            per-account targets for a fiscal period.
     *
     * Each row is segregated by IFRS entity_id (one entity per tenant today, but
     * scoped for correctness). Journal/budget lines live in a JSON column so the
     * shape round-trips 1:1 with the frontend types.
     */
    return new class extends Migration {
        public function up() : void
        {
            if ( ! Schema::hasTable( 'accounting_recurring_journals' ) ) {
                Schema::create( 'accounting_recurring_journals', function (Blueprint $table) {
                    $table->bigIncrements( 'id' );
                    $table->unsignedBigInteger( 'entity_id' )->index();
                    $table->string( 'name' );
                    $table->string( 'frequency' )->default( 'MONTHLY' );
                    $table->date( 'start_date' );
                    $table->date( 'next_run_date' );
                    $table->date( 'end_date' )->nullable();
                    $table->text( 'narration' )->nullable();
                    $table->string( 'reference' )->nullable();
                    $table->string( 'branch_id' )->nullable()->index();
                    $table->json( 'lines' );
                    $table->boolean( 'active' )->default( TRUE );
                    $table->date( 'last_run_date' )->nullable();
                    $table->timestamps();
                } );
            }

            if ( ! Schema::hasTable( 'accounting_deferrals' ) ) {
                Schema::create( 'accounting_deferrals', function (Blueprint $table) {
                    $table->bigIncrements( 'id' );
                    $table->unsignedBigInteger( 'entity_id' )->index();
                    $table->string( 'name' );
                    $table->string( 'kind' )->default( 'PREPAYMENT' );
                    $table->decimal( 'total_amount', 18, 4 )->default( 0 );
                    $table->unsignedBigInteger( 'expense_account_id' );
                    $table->unsignedBigInteger( 'balance_account_id' );
                    $table->date( 'start_date' );
                    $table->unsignedInteger( 'months' )->default( 1 );
                    $table->decimal( 'recognized_amount', 18, 4 )->default( 0 );
                    $table->string( 'branch_id' )->nullable()->index();
                    $table->string( 'status' )->default( 'ACTIVE' );
                    $table->timestamps();
                } );
            }

            if ( ! Schema::hasTable( 'accounting_budgets' ) ) {
                Schema::create( 'accounting_budgets', function (Blueprint $table) {
                    $table->bigIncrements( 'id' );
                    $table->unsignedBigInteger( 'entity_id' )->index();
                    $table->string( 'name' );
                    $table->unsignedBigInteger( 'period_id' )->index();
                    $table->json( 'lines' );
                    $table->timestamps();
                } );
            }
        }

        public function down() : void
        {
            Schema::dropIfExists( 'accounting_budgets' );
            Schema::dropIfExists( 'accounting_deferrals' );
            Schema::dropIfExists( 'accounting_recurring_journals' );
        }
    };
