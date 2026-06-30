<?php

    namespace App\Http\Resources\Accounting;

    use App\Models\Accounting\Deferral;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Deferral */
    class DeferralResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'               => $this->id,
                'name'             => $this->name,
                'kind'             => $this->kind,
                'totalAmount'      => (float) $this->total_amount,
                'expenseAccountId' => $this->expense_account_id,
                'balanceAccountId' => $this->balance_account_id,
                'startDate'        => optional( $this->start_date )->toDateString(),
                'months'           => (int) $this->months,
                'recognizedAmount' => (float) $this->recognized_amount,
                'branchId'         => $this->branch_id,
                'status'           => $this->status,
            ];
        }
    }
