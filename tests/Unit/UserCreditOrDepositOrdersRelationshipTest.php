<?php

use App\Enums\PaymentType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

uses(TestCase::class);

it('defines the credit or deposit orders relationship', function () {
    $relation = (new User)->creditOrDepositOrders();

    expect($relation)->toBeInstanceOf(HasMany::class)
        ->and($relation->getRelated())->toBeInstanceOf(Order::class)
        ->and($relation->getQuery()->toSql())->toContain('payment_type')
        ->and($relation->getQuery()->getBindings())->toContain(PaymentType::CREDIT->value)
        ->and($relation->getQuery()->getBindings())->toContain(PaymentType::DEPOSIT->value);
});
