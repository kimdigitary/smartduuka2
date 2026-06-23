<?php

namespace App\Http\Controllers\Reports;

use App\Enums\RegisterStatus;
use App\Http\Resources\RegisterResource;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Register;
use App\Models\Service;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterReportController
{
    public function index(Request $request)
    {
        $dateRange = $this->dateRange($request);

        $query = Register::with($this->reportRelations($dateRange));

        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status === 'open'
                ? RegisterStatus::OPEN
                : RegisterStatus::CLOSED;

            $query->where('status', $status);
        }
//        info($dateRange);

        if ($dateRange) {
            $query->whereBetween('created_at', $dateRange);
        }


        if ($request->filled('query')) {
            $searchTerm = $request->input('query');

            $query->where(function ($q) use ($searchTerm) {
                $numericId = $searchTerm
                        |> strtoupper(...)
                        |> (fn($x) => str_replace('REG-', '', $x))
                        |> (fn($x) => ltrim($x, '0'));

                if (is_numeric($numericId)) {
                    $q->where('id', $numericId);
                }

                $q->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'ilike', "%{$searchTerm}%");
                });
            });
        }

        if ($request->boolean('unpaginated')) {
            $registers = $query->latest()->get();

            return RegisterResource::collection($registers);
        }
        info($query->toRawSql());
        $registers = $query->latest()->paginate($request->input('per_page', 15));

        return RegisterResource::collection($registers);
    }

    /**
     * @param array{0: CarbonInterface, 1: CarbonInterface}|null $dateRange
     * @return array<string, mixed>
     */
    protected function reportRelations(?array $dateRange): array
    {
        return [
            'user',
            'orders'                    => fn(Builder|Relation $query) => $this->constrainDateRange($query, 'order_datetime', $dateRange),
            'orders.posPayments'        => fn(Builder|Relation $query) => $this->constrainDateRange($query, 'date', $dateRange),
            'orders.posPayments.paymentMethod',
            'orders.taxes.tax',
            'orders.orderProducts.item' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Product::class          => ['unit', 'retailPrices'],
                    ProductVariation::class => ['product.unit', 'productAttributeOption.productAttribute', 'retailPrices'],
                    Service::class          => [],
                ]);
            },
            'posPayments'               => fn(Builder|Relation $query) => $this->constrainDateRange($query, 'date', $dateRange),
            'posPayments.paymentMethod',
            'expensesPayments'          => fn(Builder|Relation $query) => $this->constrainDateRange($query, 'date', $dateRange),
            'expensesPayments.expense',
            'walletTransactions'        => fn(Builder|Relation $query) => $this->constrainDateRange($query, 'created_at', $dateRange),
        ];
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}|null
     */
    protected function dateRange(Request $request): ?array
    {
        if (!$request->filled('start') || !$request->filled('end')) {
            return null;
        }

        return [
            Carbon::parse($request->input('start'))->copy()->startOfDay(),
            Carbon::parse($request->input('end'))->copy()->endOfDay(),
        ];
    }

    /**
     * @param array{0: CarbonInterface, 1: CarbonInterface}|null $dateRange
     */
    protected function constrainDateRange(Builder|Relation $query, string $column, ?array $dateRange): void
    {
        if ($dateRange === null) {
            return;
        }

        $query->whereBetween($column, $dateRange);
    }
}
