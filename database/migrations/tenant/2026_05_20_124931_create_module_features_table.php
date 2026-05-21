<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up(): void
        {
            Schema::create('module_features', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('system_module_id')
                      ->constrained('system_modules')
                      ->onDelete('cascade');
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('module_features');
        }
    };