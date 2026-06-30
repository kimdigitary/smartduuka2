<?php

    namespace App\Http\Resources\Accounting;

    use App\Models\Accounting\Assignment;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Assignment */
    class AssignmentResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'            => $this->id,
                'transactionId' => $this->transaction_id,
                'clearedId'     => $this->cleared_id,
                'amount'        => (float) $this->amount,
                'date'          => optional( $this->date )->toDateString(),
            ];
        }
    }
