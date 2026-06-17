<?php

use App\Enums\CurrencyPosition;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PosPayment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

it('reports the previous and current balance from the latest payment', function () {
    config([
        'system.currency_decimal_point' => 0,
        'system.currency_position' => CurrencyPosition::RIGHT,
        'system.date_format' => 'Y-m-d',
        'system.time_format' => 'H:i',
    ]);

    Cache::shouldReceive('rememberForever')
        ->andReturn('UGX');

    $cash = new PaymentMethod(['name' => 'Cash']);
    $cash->id = 1;

    $firstPayment = new PosPayment(['amount' => 60]);
    $firstPayment->id = 1;
    $firstPayment->setRelation('paymentMethod', $cash);

    $latestPayment = new PosPayment(['amount' => 40]);
    $latestPayment->id = 2;
    $latestPayment->setRelation('paymentMethod', $cash);

    $order = new Order([
        'total' => 100,
        'subtotal' => 100,
        'tax' => 0,
        'discount' => 0,
        'shipping_charge' => 0,
        'paid' => 100,
        'change' => 0,
        'order_datetime' => now(),
    ]);
    $order->id = 1;
    $order->setRelation('posPayments', new Collection([$firstPayment, $latestPayment]));

    $resource = (new OrderResource($order))->toArray(Request::create('/'));

    expect($resource['last_paid']['amount'])->toBe('40 UGX')
        ->and($resource['last_paid']['previous_balance'])->toBe('40 UGX')
        ->and($resource['balance'])->toBe(0.0)
        ->and($resource['balance_currency'])->toBe('0 UGX');
});
