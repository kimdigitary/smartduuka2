<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['tenant_branch_id']);

            // Re-add the foreign key constraint with onDelete('cascade')
            $table->foreign('tenant_branch_id')
                  ->references('id')
                  ->on('tenant_branches')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Drop the cascading foreign key constraint
            $table->dropForeign(['tenant_branch_id']);

            // Re-add the original foreign key constraint
            $table->foreign('tenant_branch_id')
                  ->references('id')
                  ->on('tenant_branches');
        });
    }
};
