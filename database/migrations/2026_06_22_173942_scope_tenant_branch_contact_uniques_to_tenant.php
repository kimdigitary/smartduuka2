<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenant_branches', function (Blueprint $table) {
            $table->dropUnique('tenant_branches_email_unique');
            $table->dropUnique('tenant_branches_website_unique');
            $table->dropUnique('tenant_branches_phone2_unique');

            $table->unique(['tenant_id', 'email'], 'tenant_branches_tenant_id_email_unique');
            $table->unique(['tenant_id', 'phone'], 'tenant_branches_tenant_id_phone_unique');
            $table->unique(['tenant_id', 'phone2'], 'tenant_branches_tenant_id_phone2_unique');
            $table->unique(['tenant_id', 'website'], 'tenant_branches_tenant_id_website_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_branches', function (Blueprint $table) {
            $table->dropUnique('tenant_branches_tenant_id_email_unique');
            $table->dropUnique('tenant_branches_tenant_id_phone_unique');
            $table->dropUnique('tenant_branches_tenant_id_phone2_unique');
            $table->dropUnique('tenant_branches_tenant_id_website_unique');

            $table->unique('email', 'tenant_branches_email_unique');
            $table->unique('website', 'tenant_branches_website_unique');
            $table->unique('phone2', 'tenant_branches_phone2_unique');
        });
    }
};
