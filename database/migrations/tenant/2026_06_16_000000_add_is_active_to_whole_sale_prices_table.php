<?php

use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('whole_sale_prices', 'is_active')) {
            Schema::table('whole_sale_prices', function (Blueprint $table) {
                $table->unsignedTinyInteger('is_active')->default(Status::ACTIVE);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('whole_sale_prices', 'is_active')) {
            Schema::table('whole_sale_prices', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
