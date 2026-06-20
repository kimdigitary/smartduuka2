<?php

use App\Services\DashboardService;
use Tests\TestCase;

uses(TestCase::class);

function callDashboardServicePercentageHelper(string $method, int|float $current, int|float $previous): float
{
    $reflection = new ReflectionMethod(DashboardService::class, $method);

    return $reflection->invoke(new DashboardService, $current, $previous);
}

it('calculates quotation stat percentage changes', function (int|float $current, int|float $previous, float $expected) {
    expect(callDashboardServicePercentageHelper('percentageChange', $current, $previous))->toBe($expected);
})->with([
    'increase' => [48, 40, 20.0],
    'decrease' => [32, 40, -20.0],
    'new activity' => [5, 0, 100.0],
    'no activity' => [0, 0, 0.0],
]);

it('calculates quotation conversion rates', function () {
    expect(callDashboardServicePercentageHelper('percentageOf', 32, 48))->toBe(66.7)
        ->and(callDashboardServicePercentageHelper('percentageOf', 0, 0))->toBe(0.0);
});
