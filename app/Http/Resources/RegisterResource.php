<?php

namespace App\Http\Resources;

use App\Enums\DefaultPaymentMethods;
use App\Enums\ExpenseNature;
use App\Enums\PaymentType;
use App\Enums\PosPaymentType;
use App\Libraries\AppLibrary;
use App\Models\ExpensePayment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Register;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** @mixin Register */
class RegisterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $orders = $this->loadedCollection('orders');
        $posPayments = $this->loadedCollection('posPayments');
        $expensesPayments = $this->loadedCollection('expensesPayments');
        $walletTransactions = $this->loadedCollection('walletTransactions');

        $allProducts = $orders->flatMap(function (Order $order) {
            return $this->modelRelationCollection($order, 'orderProducts');
        });

        $groupedItems = $allProducts->groupBy(function ($item) {
            return $item->item_id . '-' . $item->item_type;
        })->map(function ($group) {
            $firstItem = $this->modelRelation($group->first(), 'item');
            $totalQuantity = $group->sum('quantity');
            $quantity_picked = $group->sum('quantity_picked');
            $buyingPrice = $this->itemBuyingPrice($firstItem);
            $totalCost = $totalQuantity * $buyingPrice;

            $name = $this->itemName($firstItem);
            $reserved = $totalQuantity - $quantity_picked;
            $damages = 0;
            if (!$firstItem instanceof Service) {
                $damages = $this->reportAggregate($firstItem, 'report_damages');
            }

            $unit = $this->itemUnit($firstItem);

            return [
                'item_id'              => $firstItem?->id,
                'name'                 => $name,
                'damages'              => $damages,
                'damages_value'        => $damages * $buyingPrice,
                'stock'                => $this->itemStock($firstItem),
                'reserved'             => $reserved,
                'reserved_value'       => $reserved * $buyingPrice,
                'unit'                 => $unit ? new UnitResource($unit) : null,
                'quantity'             => $totalQuantity,
                'total_sales'          => $group->sum('total'),
                'total_sales_currency' => AppLibrary::currencyAmountFormat($group->sum('total')),
                'total_cost'           => $totalCost,
                'total_cost_currency'  => AppLibrary::currencyAmountFormat($totalCost),
            ];
        })->values();

        $paymentSummary = $posPayments->groupBy('payment_method_id')->map(function ($group) {
            $methodName = $this->paymentMethodName($group->first()) ?? 'Unknown';
            $totalAmount = $group->sum('amount');

            return [
                'payment_method_id' => $group->first()->payment_method_id,
                'name'              => $methodName,
                'total'             => $totalAmount,
                'total_currency'    => AppLibrary::currencyAmountFormat($totalAmount),
            ];
        })->values();

        // --- 1. ACCRUAL ACCOUNTING (TRADING PERFORMANCE) ---
        $grandTotalCost = $groupedItems->sum('total_cost');
        $total_sales_value = $groupedItems->sum('total_sales'); // NEW: Value of all items sold today (Cash + Credit)

        // FIXED: Gross Profit is now strictly Items Sold Value - Items Cost
        $profit = $total_sales_value - $grandTotalCost;

        // --- 2. CASH FLOW (DRAWER REALITY) ---
        // Money actually handed to cashier today (Cash sales + Old debts paid)
        $total_revenue = $posPayments->sum('amount');

        $reserved_value = $groupedItems->sum('reserved_value');
        $damages_value = $groupedItems->sum('damages_value');

        // --- 3. EXPENSES & NET PROFIT ---
        // FIXED: Bulletproof Enum checking using ->value to prevent silent type-mismatch failures
        $expenses_items = $expensesPayments->map(function (ExpensePayment $expense_payment) {
            return $this->modelRelation($expense_payment, 'expense');
        })->filter(function ($expense) {
            return $expense && (
                    $expense->expense_nature === ExpenseNature::OPERATIONAL ||
                    (isset($expense->expense_nature->value) && $expense->expense_nature->value === ExpenseNature::OPERATIONAL->value)
                );
        })->unique('id')->values();

        $expenses = $expensesPayments->sum(function (ExpensePayment $expense_payment) {
            $expense = $this->modelRelation($expense_payment, 'expense');
            if ($expense && (
                    $expense->expense_nature === ExpenseNature::OPERATIONAL ||
                    (isset($expense->expense_nature->value) && $expense->expense_nature->value === ExpenseNature::OPERATIONAL->value)
                )) {
                return $expense_payment->amount;
            }

            return 0;
        });

        $net_profit = $profit - $expenses;

        // --- 4. CREDIT AND DEPOSITS ---
        $totalCreditRemaining = $orders
            ->where('payment_type', PaymentType::CREDIT)
            ->sum(fn(Order $order) => $this->orderBalance($order));

        $deposits = $orders
            ->where('payment_type', '<>', PaymentType::CASH)
            ->sum(function (Order $order) {
                return $this->modelRelationCollection($order, 'posPayments')->sum('amount');
            });

        $total_order_cost = $grandTotalCost;
        $wallet_transactions = $walletTransactions->sum('amount');

        $expected_float = $this->opening_float + (
            $posPayments
                ->reject(fn($payment) => $this->paymentMethodName($payment) === DefaultPaymentMethods::WALLET->value)
                ->sum('amount')
            );

        $total_debt_paid = $posPayments
            ->filter(fn($payment) => $payment->pos_payment_type === PosPaymentType::DEBT)
            ->sum('amount');

        return [
            'id'                           => 'REG-' . Str::padLeft($this->id, 5, '0'),
            'opening_float'                => $this->opening_float,
            'opening_float_currency'       => AppLibrary::currencyAmountFormat($this->opening_float),
            'reserved_value'               => currency($reserved_value),
            'damages_value'                => currency($damages_value),
            'notes'                        => $this->notes,
            'expected_float'               => $expected_float,
            'expected_float_currency'      => AppLibrary::currencyAmountFormat($expected_float),
            'closing_float'                => $this->closing_float,
            'closing_float_currency'       => AppLibrary::currencyAmountFormat($this->closing_float),
            'difference'                   => $this->difference,
            'difference_currency'          => AppLibrary::currencyAmountFormat($this->difference),
            'closed_at'                    => datetime($this->closed_at),
            'created_at'                   => AppLibrary::datetime2($this->created_at),
            'user_id'                      => $this->user_id,
            'user'                         => $this->userSummary(),

            // NEW: Exposing true trading sales to the frontend
            'total_sales_value'            => $total_sales_value,
            'total_sales_value_currency'   => AppLibrary::currencyAmountFormat($total_sales_value),

            // Existing flow metrics
            'sales'                        => $total_revenue,
            'sales_currency'               => AppLibrary::currencyAmountFormat($total_revenue),
            'expense'                      => $expenses,
            'expenses'                     => $expenses_items->map(fn($expense) => $this->expenseSummary($expense))->values(),
            'expense_currency'             => currency($expenses),
            'posPayments'                  => PosPaymentResource::collection($posPayments),
            'item_summary'                 => $groupedItems,
            'payment_summary'              => $paymentSummary,
            'total_cost_of_goods'          => $grandTotalCost,
            'total_cost_of_goods_currency' => AppLibrary::currencyAmountFormat($grandTotalCost),

            // Credit / Debt metrics
            'total_credit'                 => $totalCreditRemaining,
            'wallet_transactions'          => $wallet_transactions,
            'wallet_transactions_currency' => currency($wallet_transactions),
            'total_credit_currency'        => AppLibrary::currencyAmountFormat($totalCreditRemaining),
            'total_debt_paid'              => currency($total_debt_paid),
            'deposits'                     => $deposits,
            'deposits_currency'            => currency($deposits),

            // Corrected Profit Metrics
            'profit'                       => $profit,
            'status'                       => $this->status,
            'profit_currency'              => AppLibrary::currencyAmountFormat($profit),
            'net_profit'                   => $net_profit,
            'net_profit_currency'          => currency($net_profit),

            'orders'                 => $request->boolean('include_orders')
                ? OrderResource::collection($orders)
                : [],
            'total_order_cost'       => $total_order_cost,
            'total_revenue'          => $total_revenue,
            'total_revenue_currency' => currency($total_revenue),
        ];
    }

    protected function loadedCollection(string $relation): Collection
    {
        return $this->modelRelationCollection($this->resource, $relation);
    }

    protected function userSummary(): ?array
    {
        $user = $this->modelRelation($this->resource, 'user');

        if (!$user) {
            return null;
        }

        return [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ];
    }

    protected function expenseSummary(mixed $expense): ?array
    {
        if (!$expense) {
            return null;
        }

        $balance = (float)$expense->amount - (float)$expense->paid;

        return [
            'id'               => $expense->id,
            'expense_id'       => $expense->expense_id ?? $expense->id,
            'name'             => $expense->name,
            'expense_nature'   => $expense->expense_nature,
            'payment_status'   => $expense->payment_status,
            'amount'           => $expense->amount,
            'amount_currency'  => AppLibrary::currencyAmountFormat($expense->amount),
            'date'             => $expense->date ? AppLibrary::datetime2($expense->date) : '',
            'note'             => $expense->note,
            'expense_type'     => $expense->expense_type,
            'referenceNo'      => $expense->reference_no,
            'balance'          => $balance,
            'balance_currency' => AppLibrary::currencyAmountFormat($balance),
            'paid'             => $expense->paid,
            'paid_currency'    => AppLibrary::currencyAmountFormat($expense->paid),
        ];
    }

    protected function modelRelationCollection(mixed $model, string $relation): Collection
    {
        $value = $this->modelRelation($model, $relation);

        if ($value instanceof Collection) {
            return $value;
        }

        return $value ? collect([$value]) : collect();
    }

    protected function modelRelation(mixed $model, string $relation): mixed
    {
        if (!$model instanceof EloquentModel || !$model->relationLoaded($relation)) {
            return null;
        }

        return $model->getRelation($relation);
    }

    protected function itemName(mixed $item): ?string
    {
        if ($item instanceof ProductVariation) {
            $product = $this->modelRelation($item, 'product');
            $option = $this->modelRelation($item, 'productAttributeOption');
            $attribute = $option ? $this->modelRelation($option, 'productAttribute') : null;

            if ($product && $option && $attribute) {
                return $product->name . ' - ' . $attribute->name . ' (' . $option->name . ')';
            }

            return $product?->name;
        }

        return $item?->name;
    }

    protected function itemBuyingPrice(mixed $item): float
    {
        if (!$item || $item instanceof Service) {
            return 0;
        }

        $retailPrices = $this->modelRelationCollection($item, 'retailPrices');
        $retailPrice = $retailPrices->first();

        if ($retailPrice) {
            return (float)$retailPrice->buying_price;
        }

        if ($item instanceof Product) {
            return (float)$item->getRawOriginal('buying_price');
        }

        return (float)($item->getAttributes()['buying_price'] ?? 0);
    }

    protected function itemStock(mixed $item): float
    {
        if (!$item || $item instanceof Service) {
            return 0;
        }

        return $this->reportAggregate($item, 'report_stock');
    }

    protected function itemUnit(mixed $item): mixed
    {
        if ($item instanceof ProductVariation) {
            $product = $this->modelRelation($item, 'product');

            return $product ? $this->modelRelation($product, 'unit') : null;
        }

        if ($item instanceof Product) {
            return $this->modelRelation($item, 'unit');
        }

        return null;
    }

    protected function orderBalance(Order $order): float
    {
        if ($order->relationLoaded('posPayments')) {
            return (float)$order->total - (float)$this->modelRelationCollection($order, 'posPayments')->sum('amount');
        }

        return (float)($order->getRawOriginal('balance') ?? 0);
    }

    protected function paymentMethodName(mixed $payment): ?string
    {
        return $this->modelRelation($payment, 'paymentMethod')?->name;
    }

    protected function reportAggregate(mixed $model, string $attribute): float
    {
        if ($model && array_key_exists($attribute, $model->getAttributes())) {
            return (float)$model->getAttribute($attribute);
        }

        return 0;
    }
}
