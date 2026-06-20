<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PreOrderStatus;
use App\Enums\QuotationStatus;
use App\Enums\ReturnStatus;
use App\Models\Order;
use Tests\TestCase;

uses(TestCase::class);

it('only includes converted quotations in the active scope', function () {
    $query = Order::active();

    expect($query->toSql())->toContain('("order_type" != ? or "quotation_status" = ?)')
        ->and($query->getBindings())->toBe([
            ReturnStatus::CANCELED->value,
            ReturnStatus::REJECTED->value,
            PreOrderStatus::REFUNDED->value,
            PreOrderStatus::CANCELED->value,
            OrderStatus::CANCELED->value,
            OrderType::QUOTATION->value,
            QuotationStatus::CONVERTED->value,
        ]);
});
