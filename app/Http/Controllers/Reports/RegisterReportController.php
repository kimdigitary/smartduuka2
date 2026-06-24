<?php

namespace App\Http\Controllers\Reports;

use App\Enums\RegisterStatus;
use App\Enums\StockStatus;
use App\Http\Resources\RegisterResource;
use App\Models\Damage;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Register;
use App\Models\Service;
use App\Models\Stock;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RegisterReportController
{
    public function index(Request $request)
    {
        $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
            'status' => ['nullable', 'in:all,open,closed'],
            'query' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'include_orders' => ['nullable', 'boolean'],
        ]);

        $dateRange = $this->dateRange($request);

        $query = $this->registerQuery($dateRange);

        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status === 'open'
                ? RegisterStatus::OPEN
                : RegisterStatus::CLOSED;

            $query->where('status', $status);
        }

        if ($request->filled('query')) {
            $searchTerm = $request->input('query');

            $query->where(function ($q) use ($searchTerm) {
                $numericId = $searchTerm
                        |> strtoupper(...)
                        |> (fn ($x) => str_replace('REG-', '', $x))
                        |> (fn ($x) => ltrim($x, '0'));

                if (is_numeric($numericId)) {
                    $q->where('id', $numericId);
                }

                $q->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'ilike', "%{$searchTerm}%");
                });
            });
        }

        $registers = $query->latest()->paginate($this->perPage($request));

        $this->hydrateReportItemAggregates($registers->getCollection());

        return RegisterResource::collection($registers);
    }

    /**
     * @param  array{0: CarbonInterface, 1: CarbonInterface}|null  $dateRange
     */
    protected function registerQuery(?array $dateRange): Builder
    {
        return Register::query()
            ->when($dateRange, fn (Builder $query) => $query->whereBetween('created_at', $dateRange))
            ->with($this->reportRelations($dateRange));
    }

    /**
     * @param  array{0: CarbonInterface, 1: CarbonInterface}|null  $dateRange
     * @return array<string, mixed>
     */
    protected function reportRelations(?array $dateRange): array
    {
        return [
            'user',
            'orders' => fn (Builder|Relation $query) => $this->constrainDateRange($query, 'order_datetime', $dateRange),
            'orders.posPayments' => fn (Builder|Relation $query) => $this->constrainDateRange($query, 'date', $dateRange),
            'orders.posPayments.paymentMethod',
            'orders.taxes.tax',
            'orders.orderProducts.item' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Product::class => ['unit', 'retailPrices'],
                    ProductVariation::class => ['product.unit', 'productAttributeOption.productAttribute', 'retailPrices'],
                    Service::class => [],
                ]);
            },
            'posPayments' => fn (Builder|Relation $query) => $this->constrainDateRange($query, 'date', $dateRange),
            'posPayments.paymentMethod',
            'expensesPayments' => fn (Builder|Relation $query) => $this->constrainDateRange($query, 'date', $dateRange),
            'expensesPayments.expense' => fn (Builder|Relation $query) => $this->constrainDateRange($query, 'date', $dateRange),
            'walletTransactions' => fn (Builder|Relation $query) => $this->constrainDateRange($query, 'created_at', $dateRange),
        ];
    }

    protected function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 15), 1), 50);
    }

    protected function hydrateReportItemAggregates(Collection|EloquentCollection $registers): void
    {
        $items = $registers
            ->flatMap(fn (Register $register) => $register->orders)
            ->flatMap(fn ($order) => $order->orderProducts)
            ->map(fn ($orderProduct) => $orderProduct->item)
            ->filter(fn ($item) => $item instanceof Product || $item instanceof ProductVariation)
            ->unique(fn (Model $item) => $item::class.':'.$item->getKey())
            ->values();

        $this->applyReportAggregates($items, Product::class);
        $this->applyReportAggregates($items, ProductVariation::class);
    }

    /**
     * @param  class-string<Product|ProductVariation>  $itemClass
     */
    protected function applyReportAggregates(Collection|EloquentCollection $items, string $itemClass): void
    {
        $items = $items->filter(fn ($item) => $item instanceof $itemClass);

        if ($items->isEmpty()) {
            return;
        }

        $itemIds = $items->pluck('id')->filter()->unique()->values();

        $stockByItem = Stock::query()
            ->where('item_type', $itemClass)
            ->whereIn('item_id', $itemIds)
            ->where('status', StockStatus::RECEIVED->value)
            ->selectRaw('item_id, COALESCE(SUM(quantity), 0) as aggregate')
            ->groupBy('item_id')
            ->pluck('aggregate', 'item_id');

        $damagesByItem = Stock::query()
            ->where('item_type', $itemClass)
            ->where('model_type', Damage::class)
            ->whereIn('item_id', $itemIds)
            ->selectRaw('item_id, ABS(COALESCE(SUM(quantity), 0)) as aggregate')
            ->groupBy('item_id')
            ->pluck('aggregate', 'item_id');

        $items->each(function (Product|ProductVariation $item) use ($stockByItem, $damagesByItem): void {
            $item->setAttribute('report_stock', (float) ($stockByItem[$item->id] ?? 0));
            $item->setAttribute('report_damages', (float) ($damagesByItem[$item->id] ?? 0));
        });
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}|null
     */
    protected function dateRange(Request $request): ?array
    {
        if (! $request->filled('start') || ! $request->filled('end')) {
            return null;
        }

        return [
            Carbon::parse($request->input('start'))->copy()->startOfDay(),
            Carbon::parse($request->input('end'))->copy()->endOfDay(),
        ];
    }

    /**
     * @param  array{0: CarbonInterface, 1: CarbonInterface}|null  $dateRange
     */
    protected function constrainDateRange(Builder|Relation $query, string $column, ?array $dateRange): void
    {
        if ($dateRange === null) {
            return;
        }

        $query->whereBetween($column, $dateRange);
    }
}
