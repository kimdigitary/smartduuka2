<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $balance = $this->preloadedAggregate('preloaded_balance', fn () => $this->balance);
        $totalIn = $this->preloadedAggregate('preloaded_total_in', fn () => $this->total_in);
        $totalOut = $this->preloadedAggregate('preloaded_total_out', fn () => $this->total_out);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'merchant_code' => $this->merchant_code,
            'balance' => $balance,
            'transactions' => $this->when(
                $this->relationLoaded('transactions'),
                function () {
                    return PaymentMethodTransactionResource::collection($this->transactions->take(5));
                }
            ),
            'total_in' => currency($totalIn),
            'total_out' => currency(abs($totalOut)),
            'balance_currency' => AppLibrary::currencyAmountFormat($balance),
        ];
    }

    protected function preloadedAggregate(string $attribute, callable $fallback): float
    {
        if (! $this->resource instanceof Model) {
            return 0;
        }

        if (array_key_exists($attribute, $this->resource->getAttributes())) {
            return (float) $this->resource->getAttribute($attribute);
        }

        return (float) $fallback();
    }
}
