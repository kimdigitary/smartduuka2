<?php

use App\Http\Controllers\Reports\RegisterReportController;
use App\Models\CustomerWalletTransaction;
use App\Models\ExpensePayment;
use App\Models\Order;
use App\Models\PosPayment;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class);

function registerReportController(): RegisterReportController
{
    return new class extends RegisterReportController
    {
        public function publicDateRange(Request $request): ?array
        {
            return $this->dateRange($request);
        }

        public function publicReportRelations(?array $dateRange): array
        {
            return $this->reportRelations($dateRange);
        }
    };
}

it('builds a full day report date range from the request', function () {
    $range = registerReportController()->publicDateRange(Request::create('/', 'GET', [
        'start' => '2026-06-01',
        'end' => '2026-06-10',
    ]));

    expect($range[0]->toDateTimeString())->toBe('2026-06-01 00:00:00')
        ->and($range[1]->toDateTimeString())->toBe('2026-06-10 23:59:59');
});

it('constrains report relation queries to the requested date range', function () {
    $dateRange = [
        Carbon::parse('2026-06-01')->startOfDay(),
        Carbon::parse('2026-06-10')->endOfDay(),
    ];

    $relations = registerReportController()->publicReportRelations($dateRange);

    $expectations = [
        'orders' => [Order::query(), 'order_datetime'],
        'orders.posPayments' => [PosPayment::query(), 'date'],
        'posPayments' => [PosPayment::query(), 'date'],
        'expensesPayments' => [ExpensePayment::query(), 'date'],
        'walletTransactions' => [CustomerWalletTransaction::query(), 'created_at'],
    ];

    foreach ($expectations as $relation => [$query, $column]) {
        $relations[$relation]($query);

        $where = collect($query->getQuery()->wheres)->firstWhere('column', $column);

        expect($where['type'])->toBe('between')
            ->and($where['values'])->toBe($dateRange);
    }
});

it('accepts relation instances when constraining eager loaded report relations', function () {
    $dateRange = [
        Carbon::parse('2026-06-01')->startOfDay(),
        Carbon::parse('2026-06-10')->endOfDay(),
    ];

    $relations = registerReportController()->publicReportRelations($dateRange);
    $register = new Register;
    $order = new Order;

    $expectations = [
        'orders' => [$register->orders(), 'order_datetime'],
        'orders.posPayments' => [$order->posPayments(), 'date'],
        'posPayments' => [$register->posPayments(), 'date'],
        'expensesPayments' => [$register->expensesPayments(), 'date'],
        'walletTransactions' => [$register->walletTransactions(), 'created_at'],
    ];

    foreach ($expectations as $relation => [$query, $column]) {
        $relations[$relation]($query);

        $where = collect($query->getQuery()->getQuery()->wheres)->firstWhere('column', $column);

        expect($where['type'])->toBe('between')
            ->and($where['values'])->toBe($dateRange);
    }
});
