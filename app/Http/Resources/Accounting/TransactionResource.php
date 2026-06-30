<?php

    namespace App\Http\Resources\Accounting;

    use IFRS\Models\Transaction;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Transaction */
    class TransactionResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            // We always post as compound, so the full double-entry lives in
            // lineItems: the main account leg first, then the stored line items.
            $mainLeg = [
                'id'        => 'main-' . $this->id,
                'accountId' => $this->account_id,
                'amount'    => (float) $this->main_account_amount,
                'quantity'  => 1,
                'credited'  => (bool) $this->credited,
                'vatAmount' => 0,
            ];

            $lines = $this->lineItems->map( static fn ($li) => [
                'id'        => $li->id,
                'accountId' => $li->account_id,
                'amount'    => (float) $li->amount,
                'quantity'  => (float) ( $li->quantity ?? 1 ),
                'credited'  => (bool) $li->credited,
                'vatAmount' => 0,
            ] )->all();

            return [
                'id'              => $this->id,
                'transactionType' => $this->transaction_type,
                'transactionNo'   => $this->transaction_no,
                'date'            => optional( $this->transaction_date )->toDateString(),
                'narration'       => $this->narration,
                'accountId'       => $this->account_id,
                'currencyId'      => $this->currency_id,
                'reference'       => $this->reference,
                'credited'        => (bool) $this->credited,
                'compound'        => TRUE,
                'isPosted'        => TRUE,
                'branchId'        => $this->branch_id,
                'source'          => $this->source,
                'lineItems'       => array_merge( [ $mainLeg ], $lines ),
            ];
        }
    }
