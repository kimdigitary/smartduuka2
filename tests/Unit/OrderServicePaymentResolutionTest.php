<?php

use App\Services\OrderService;
use Tests\TestCase;

uses(TestCase::class);

function callOrderServicePaymentHelper(string $method, array $payment): mixed
{
    $reflection = new ReflectionMethod(OrderService::class, $method);

    return $reflection->invoke(new OrderService, $payment);
}

it('resolves payment method ids from supported payment row keys', function (array $payment) {
    expect(callOrderServicePaymentHelper('paymentMethodId', $payment))->toBe(7);
})->with([
    'id' => [['id' => 7, 'amount' => 100]],
    'payment_method_id' => [['payment_method_id' => 7, 'amount' => 100]],
    'paymentMethodId' => [['paymentMethodId' => 7, 'amount' => 100]],
    'payment_method' => [['payment_method' => 7, 'amount' => 100]],
]);

it('defaults missing payment amounts to zero', function () {
    expect(callOrderServicePaymentHelper('paymentAmount', []))->toBe(0.0);
});

it('throws a clear error when a positive payment has no payment method id', function () {
    callOrderServicePaymentHelper('paymentMethodId', ['amount' => 100]);
})->throws(Exception::class, 'Payment method is required.');
