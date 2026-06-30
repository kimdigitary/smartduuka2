<?php

use App\Enums\StockStatus;
use App\Models\Product;
use App\Models\Stock;
use App\Notifications\LowStockAlert;
use App\Services\LowStockNotifier;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Smartisan\Settings\Facades\Settings;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Settings::shouldReceive('group')
        ->with('notification')
        ->andReturnSelf();
    Settings::shouldReceive('all')
        ->andReturn([
            'admin_email' => 'admin@example.com',
            'admin_phone' => '+256700000000',
        ]);
    Settings::shouldReceive('get')
        ->with('events')
        ->andReturn(json_encode([
            [
                'id' => 'low_stock',
                'channels' => [
                    'email' => true,
                    'sms' => false,
                    'whatsapp' => false,
                    'system' => false,
                ],
            ],
        ]));

    Schema::dropIfExists('stocks');
    Schema::dropIfExists('products');
    Schema::dropIfExists('product_categories');

    Schema::create('product_categories', function ($table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('products', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('sku')->unique();
        $table->foreignId('product_category_id')->nullable();
        $table->unsignedTinyInteger('status')->default(5);
        $table->unsignedSmallInteger('track_stock')->default(0);
        $table->unsignedBigInteger('low_stock_quantity_warning')->default(1);
        $table->softDeletes();
        $table->timestamps();
    });

    Schema::create('stocks', function ($table) {
        $table->id();
        $table->foreignId('product_id');
        $table->string('model_type');
        $table->unsignedBigInteger('model_id');
        $table->string('item_type');
        $table->unsignedBigInteger('item_id');
        $table->string('sku')->nullable();
        $table->decimal('quantity')->default(1);
        $table->unsignedTinyInteger('status')->default(5);
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('stocks');
    Schema::dropIfExists('products');
    Schema::dropIfExists('product_categories');
});

test('it sends low stock notifications for tracked products during login checks', function () {
    Notification::fake();

    $lowStockProduct = Product::query()->create([
        'name' => 'Tracked Low Product',
        'slug' => 'tracked-low-product',
        'sku' => 'LOW-001',
        'track_stock' => 1,
        'low_stock_quantity_warning' => 5,
    ]);
    $healthyProduct = Product::query()->create([
        'name' => 'Tracked Healthy Product',
        'slug' => 'tracked-healthy-product',
        'sku' => 'OK-001',
        'track_stock' => 1,
        'low_stock_quantity_warning' => 5,
    ]);
    $untrackedProduct = Product::query()->create([
        'name' => 'Untracked Low Product',
        'slug' => 'untracked-low-product',
        'sku' => 'NO-001',
        'track_stock' => 0,
        'low_stock_quantity_warning' => 5,
    ]);

    Stock::query()->create([
        'product_id' => $lowStockProduct->id,
        'model_type' => Product::class,
        'model_id' => $lowStockProduct->id,
        'item_type' => Product::class,
        'item_id' => $lowStockProduct->id,
        'quantity' => 4,
        'status' => StockStatus::RECEIVED,
    ]);
    Stock::query()->create([
        'product_id' => $healthyProduct->id,
        'model_type' => Product::class,
        'model_id' => $healthyProduct->id,
        'item_type' => Product::class,
        'item_id' => $healthyProduct->id,
        'quantity' => 6,
        'status' => StockStatus::RECEIVED,
    ]);
    Stock::query()->create([
        'product_id' => $untrackedProduct->id,
        'model_type' => Product::class,
        'model_id' => $untrackedProduct->id,
        'item_type' => Product::class,
        'item_id' => $untrackedProduct->id,
        'quantity' => 1,
        'status' => StockStatus::RECEIVED,
    ]);

    LowStockNotifier::checkTrackedProductsForLogin();

    Notification::assertSentOnDemand(LowStockAlert::class, function (LowStockAlert $notification) {
        return $notification->productName === 'Tracked Low Product'
            && $notification->sku === 'LOW-001'
            && $notification->currentStock === 4.0
            && $notification->lowStockThreshold === 5.0
            && $notification->triggeredBy === 'login';
    });

    Notification::assertSentOnDemandTimes(LowStockAlert::class, 1);
});
