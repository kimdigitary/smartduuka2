<?php

use App\Http\Controllers\Reports\RegisterReportController;
use App\Http\Resources\RegisterResource;
use App\Models\CustomerWalletTransaction;
use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PosPayment;
use App\Models\Product;
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

        public function publicReportRelations(?array $dateRange, bool $includeOrders = false): array
        {
            return $this->reportRelations($dateRange, $includeOrders);
        }

        public function publicRegisterQuery(?array $dateRange)
        {
            return $this->registerQuery($dateRange);
        }

        public function publicPerPage(Request $request): int
        {
            return $this->perPage($request);
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
        'expensesPayments.expense' => [Expense::query(), 'date'],
        'walletTransactions' => [CustomerWalletTransaction::query(), 'created_at'],
    ];

    foreach ($expectations as $relation => [$query, $column]) {
        $relations[$relation]($query);

        $where = collect($query->getQuery()->wheres)->firstWhere('column', $column);

        expect($where['type'])->toBe('between')
            ->and($where['values'])->toBe($dateRange);
    }
});

it('constrains the base register query to the requested date range', function () {
    $dateRange = [
        Carbon::parse('2026-06-22')->startOfDay(),
        Carbon::parse('2026-06-28')->endOfDay(),
    ];

    $query = registerReportController()->publicRegisterQuery($dateRange);
    $where = collect($query->getQuery()->wheres)->firstWhere('column', 'created_at');

    expect($where['type'])->toBe('between')
        ->and($where['values'])->toBe($dateRange);
});

it('omits expanded order-only relations from the default register report query', function () {
    $relations = registerReportController()->publicReportRelations(null);

    expect($relations)->not->toHaveKey('orders.posPayments.paymentMethod')
        ->and($relations)->not->toContain('orders.taxes.tax');
});

it('includes expanded order relations only when requested', function () {
    $relations = registerReportController()->publicReportRelations(null, includeOrders: true);

    $relations['orders.posPayments.paymentMethod'](PaymentMethod::query());

    expect($relations)->toHaveKey('orders.posPayments.paymentMethod')
        ->and($relations)->toContain('orders.taxes.tax');
});

it('caps register report pagination size', function () {
    $perPage = registerReportController()->publicPerPage(Request::create('/', 'GET', [
        'per_page' => 500,
    ]));

    expect($perPage)->toBe(50);
});

it('uses precomputed item report aggregates without fallback queries', function () {
    $product = new Product([
        'name' => 'Coffee',
        'buying_price' => 10,
    ]);
    $product->id = 10;
    $product->setAttribute('report_stock', 25);
    $product->setAttribute('report_damages', 4);

    $resource = new class(new Register) extends RegisterResource
    {
        public function publicReportAggregate($model, string $attribute): float
        {
            return $this->reportAggregate($model, $attribute);
        }
    };

    expect($resource->publicReportAggregate($product, 'report_stock'))->toBe(25.0)
        ->and($resource->publicReportAggregate($product, 'report_damages'))->toBe(4.0)
        ->and($resource->publicReportAggregate($product, 'missing_report_value'))->toBe(0.0);
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
        'expensesPayments.expense' => [(new ExpensePayment)->expense(), 'date'],
        'walletTransactions' => [$register->walletTransactions(), 'created_at'],
    ];

    foreach ($expectations as $relation => [$query, $column]) {
        $relations[$relation]($query);

        $where = collect($query->getQuery()->getQuery()->wheres)->firstWhere('column', $column);

        expect($where['type'])->toBe('between')
            ->and($where['values'])->toBe($dateRange);
    }
});
