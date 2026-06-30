<?php

    namespace App\Http\Resources\Accounting;

    use App\Models\Accounting\Budget;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Budget */
    class BudgetResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'       => $this->id,
                'name'     => $this->name,
                'periodId' => $this->period_id,
                'lines'    => collect( $this->lines ?? [] )->map( static fn ($l) => [
                    'accountId' => (int) $l[ 'accountId' ],
                    'amount'    => (float) $l[ 'amount' ],
                ] )->all(),
            ];
        }
    }
