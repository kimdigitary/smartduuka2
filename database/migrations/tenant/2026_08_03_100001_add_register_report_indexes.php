<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        DB::statement('CREATE INDEX IF NOT EXISTS registers_report_date_idx ON registers (created_at DESC, id DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS registers_report_status_date_idx ON registers (status, created_at DESC, id DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS registers_report_branch_date_idx ON registers (branch_id, created_at DESC, id DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS registers_report_branch_status_date_idx ON registers (branch_id, status, created_at DESC, id DESC)');

        DB::statement('CREATE INDEX IF NOT EXISTS orders_report_register_datetime_idx ON orders (register_id, order_datetime)');
        DB::statement('CREATE INDEX IF NOT EXISTS orders_report_branch_register_datetime_idx ON orders (branch_id, register_id, order_datetime)');

        DB::statement('CREATE INDEX IF NOT EXISTS pos_payments_report_register_date_idx ON pos_payments (register_id, date)');
        DB::statement('CREATE INDEX IF NOT EXISTS pos_payments_report_branch_register_date_idx ON pos_payments (branch_id, register_id, date)');
        DB::statement('CREATE INDEX IF NOT EXISTS pos_payments_report_order_date_idx ON pos_payments (order_id, date)');
        DB::statement('CREATE INDEX IF NOT EXISTS pos_payments_report_branch_order_date_idx ON pos_payments (branch_id, order_id, date)');

        DB::statement('CREATE INDEX IF NOT EXISTS expense_payments_report_register_date_idx ON expense_payments (register_id, date)');
        DB::statement('CREATE INDEX IF NOT EXISTS expense_payments_report_branch_register_date_idx ON expense_payments (branch_id, register_id, date)');
        DB::statement('CREATE INDEX IF NOT EXISTS expense_payments_report_expense_id_idx ON expense_payments (expense_id)');

        DB::statement('CREATE INDEX IF NOT EXISTS expenses_report_date_idx ON expenses (date)');
        DB::statement('CREATE INDEX IF NOT EXISTS expenses_report_branch_date_idx ON expenses (branch_id, date)');

        DB::statement('CREATE INDEX IF NOT EXISTS wallet_transactions_report_register_type_created_idx ON customer_wallet_transactions (register_id, type, created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS wallet_transactions_report_branch_register_type_created_idx ON customer_wallet_transactions (branch_id, register_id, type, created_at)');

        DB::statement('CREATE INDEX IF NOT EXISTS order_products_report_order_id_idx ON order_products (order_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS users_report_name_trgm_idx ON users USING gin (name gin_trgm_ops)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_report_name_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS order_products_report_order_id_idx');

        DB::statement('DROP INDEX IF EXISTS wallet_transactions_report_branch_register_type_created_idx');
        DB::statement('DROP INDEX IF EXISTS wallet_transactions_report_register_type_created_idx');

        DB::statement('DROP INDEX IF EXISTS expenses_report_branch_date_idx');
        DB::statement('DROP INDEX IF EXISTS expenses_report_date_idx');

        DB::statement('DROP INDEX IF EXISTS expense_payments_report_expense_id_idx');
        DB::statement('DROP INDEX IF EXISTS expense_payments_report_branch_register_date_idx');
        DB::statement('DROP INDEX IF EXISTS expense_payments_report_register_date_idx');

        DB::statement('DROP INDEX IF EXISTS pos_payments_report_branch_order_date_idx');
        DB::statement('DROP INDEX IF EXISTS pos_payments_report_order_date_idx');
        DB::statement('DROP INDEX IF EXISTS pos_payments_report_branch_register_date_idx');
        DB::statement('DROP INDEX IF EXISTS pos_payments_report_register_date_idx');

        DB::statement('DROP INDEX IF EXISTS orders_report_branch_register_datetime_idx');
        DB::statement('DROP INDEX IF EXISTS orders_report_register_datetime_idx');

        DB::statement('DROP INDEX IF EXISTS registers_report_branch_status_date_idx');
        DB::statement('DROP INDEX IF EXISTS registers_report_branch_date_idx');
        DB::statement('DROP INDEX IF EXISTS registers_report_status_date_idx');
        DB::statement('DROP INDEX IF EXISTS registers_report_date_idx');
    }
};
