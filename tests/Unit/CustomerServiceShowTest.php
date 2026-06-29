<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        DB::connection()->getPdo()->sqliteCreateFunction('GREATEST', fn (...$values) => max($values), -1);
    }

    dropCustomerServiceShowTables();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('username')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->unsignedTinyInteger('status')->nullable();
        $table->string('phone')->nullable();
        $table->string('phone2')->nullable();
        $table->string('type')->nullable();
        $table->text('notes')->nullable();
        $table->foreignId('branch_id')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->timestamp('last_login_date')->nullable();
        $table->boolean('force_reset')->default(false);
        $table->boolean('is_reset')->default(false);
        $table->string('global_id')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id');
        $table->decimal('total', 20, 2);
        $table->unsignedTinyInteger('status');
        $table->unsignedTinyInteger('payment_type');
        $table->dateTime('order_datetime');
        $table->timestamps();
    });

    Schema::create('pos_payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->nullable();
        $table->decimal('amount', 20, 2)->default(0);
        $table->foreignId('payment_method_id')->nullable();
        $table->foreignId('branch_id')->nullable();
        $table->timestamps();
    });

    Schema::create('legacy_debts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id');
        $table->decimal('amount', 20, 2);
        $table->dateTime('date');
        $table->string('notes');
        $table->unsignedTinyInteger('payment_status')->default(1);
        $table->timestamps();
    });

    Schema::create('customer_wallet_transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id');
        $table->decimal('amount', 20, 2)->default(0);
        $table->foreignId('payment_method_id')->nullable();
        $table->string('reference')->nullable();
        $table->unsignedTinyInteger('type')->nullable();
        $table->decimal('balance', 20, 2)->default(0);
        $table->foreignId('branch_id')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id');
        $table->decimal('amount', 20, 2)->default(0);
        $table->foreignId('payment_method_id')->nullable();
        $table->unsignedTinyInteger('customer_payment_type')->nullable();
        $table->foreignId('branch_id')->nullable();
        $table->timestamps();
    });

    Schema::create('payment_methods', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_ledgers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id');
        $table->dateTime('date')->nullable();
        $table->string('reference')->nullable();
        $table->string('description')->nullable();
        $table->decimal('bill_amount', 20, 2)->default(0);
        $table->decimal('paid', 20, 2)->default(0);
        $table->decimal('balance', 20, 2)->default(0);
        $table->foreignId('branch_id')->nullable();
        $table->timestamps();
    });

    Schema::create('addresses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id');
        $table->string('full_name')->nullable();
        $table->foreignId('branch_id')->nullable();
        $table->timestamps();
    });

    Schema::create('order_products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id');
        $table->unsignedBigInteger('item_id')->nullable();
        $table->string('item_type')->nullable();
        $table->unsignedInteger('quantity')->default(1);
        $table->foreignId('branch_id')->nullable();
    });

    Schema::create('media', function (Blueprint $table) {
        $table->id();
        $table->morphs('model');
        $table->uuid('uuid')->nullable()->unique();
        $table->string('collection_name');
        $table->string('name');
        $table->string('file_name');
        $table->string('mime_type')->nullable();
        $table->string('disk');
        $table->string('conversions_disk')->nullable();
        $table->unsignedBigInteger('size');
        $table->json('manipulations');
        $table->json('custom_properties');
        $table->json('generated_conversions');
        $table->json('responsive_images');
        $table->unsignedInteger('order_column')->nullable()->index();
        $table->nullableTimestamps();
    });
});

afterEach(function () {
    dropCustomerServiceShowTables();
});

it('loads credit metrics when showing a route bound customer', function () {
    DB::table('users')->insert([
        'id' => 1,
        'name' => 'Credit Customer',
        'username' => 'credit-customer',
        'email' => 'credit@example.com',
        'password' => 'password',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('orders')->insert([
        'id' => 10,
        'user_id' => 1,
        'total' => 100,
        'status' => OrderStatus::COMPLETED->value,
        'payment_type' => PaymentType::CREDIT->value,
        'order_datetime' => now()->subDays(2),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('orders')->insert([
        'id' => 11,
        'user_id' => 1,
        'total' => 40,
        'status' => OrderStatus::COMPLETED->value,
        'payment_type' => PaymentType::CREDIT->value,
        'order_datetime' => now()->subDay(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('pos_payments')->insert([
        'order_id' => 10,
        'amount' => 25,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('pos_payments')->insert([
        'order_id' => 11,
        'amount' => 50,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('legacy_debts')->insert([
        'user_id' => 1,
        'amount' => 15,
        'date' => now(),
        'notes' => 'Opening balance',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $routeBoundCustomer = User::query()->findOrFail(1);
    $customer = app(CustomerService::class)->show($routeBoundCustomer);

    expect((float) $customer->total_credits)->toBe(90.0)
        ->and((float) $customer->credits)->toBe(90.0)
        ->and((float) $customer->order_debt_total)->toBe(75.0)
        ->and((float) $customer->legacy_debt_total)->toBe(15.0)
        ->and($customer->relationLoaded('unPaidOrders'))->toBeTrue();
});

function dropCustomerServiceShowTables(): void
{
    collect([
        'media',
        'order_products',
        'addresses',
        'customer_ledgers',
        'payment_methods',
        'customer_payments',
        'customer_wallet_transactions',
        'legacy_debts',
        'pos_payments',
        'orders',
        'users',
    ])->each(fn (string $table) => Schema::dropIfExists($table));
}
