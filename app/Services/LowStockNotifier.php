<?php

namespace App\Services;

use App\Enums\StockStatus;
use App\Models\ProductVariation;
use App\Models\Stock;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\Notification;
use Smartisan\Settings\Facades\Settings;

class LowStockNotifier
{
    public static function check(
        string  $productName,
        string  $sku,
        float   $currentStock,
        float   $lowStockThreshold,
        ?string $variationName,
        ?string $category,
        string  $triggeredBy,
    ): void
    {
        if ($currentStock > $lowStockThreshold) {
            return;
        }

        $notificationSettings = Settings::group('notification')->all();
        $adminEmail = $notificationSettings['admin_email'] ?? null;
        $adminPhone = $notificationSettings['admin_phone'] ?? null;

        $itemLabel = $variationName
            ? "{$productName} — {$variationName}"
            : $productName;

        Notification::route('mail', $adminEmail)
            ->route('sms', $adminPhone)
            ->route('whatsapp', $adminPhone)
            ->notify(new LowStockAlert(
                title: 'Low Stock Alert',
                message: "Stock for {$itemLabel} has dropped to or below the reorder level.",
                productName: $productName,
                sku: $sku,
                currentStock: $currentStock,
                lowStockThreshold: $lowStockThreshold,
                variationName: $variationName,
                category: $category,
                triggeredBy: $triggeredBy,
            ));
    }

    public static function checkTrackedProductsForLogin(): void
    {
        Stock::query()
            ->with(['item', 'product.category'])
            ->where('status', StockStatus::RECEIVED)
            ->chunkById(100, function ($stocks) {
                foreach ($stocks as $stock) {
                    $product = $stock->product;

                    if (!$product || !$product->track_stock) {
                        continue;
                    }

                    $threshold = (float)$product->low_stock_quantity_warning;

                    if ($threshold <= 0 || (float)$stock->quantity > $threshold) {
                        continue;
                    }

                    $item = $stock->item;

                    self::check(
                        productName: $product->name,
                        sku: $item->sku ?? $stock->sku ?? $product->sku,
                        currentStock: (float)$stock->quantity,
                        lowStockThreshold: $threshold,
                        variationName: self::variationLabel($item),
                        category: $product->category?->name,
                        triggeredBy: 'login',
                    );
                }
            });
    }

    private static function variationLabel(mixed $item): ?string
    {
        if (!$item instanceof ProductVariation) {
            return null;
        }

        $item->loadMissing('productAttributeOption.productAttribute');

        $attribute = $item->productAttributeOption?->productAttribute?->name;
        $option = $item->productAttributeOption?->name;

        if (!$attribute && !$option) {
            return null;
        }

        if (!$attribute) {
            return $option;
        }

        if (!$option) {
            return $attribute;
        }

        return "{$attribute} ({$option})";
    }
}
