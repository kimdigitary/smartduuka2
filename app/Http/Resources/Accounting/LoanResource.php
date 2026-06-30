<?php

    namespace App\Http\Resources\Accounting;

    use App\Models\Accounting\Loan;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Loan */
    class LoanResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                   => $this->id,
                'name'                 => $this->name,
                'lender'               => $this->lender ?? '',
                'reference'            => $this->reference,
                'principal'            => (float) $this->principal,
                'interestRate'         => (float) $this->interest_rate,
                'method'               => $this->method,
                'startDate'            => optional( $this->start_date )->toDateString(),
                'termMonths'           => (int) $this->term_months,
                'frequency'            => $this->frequency,
                'liabilityAccountId'   => $this->liability_account_id,
                'outstandingPrincipal' => (float) $this->outstanding_principal,
                'status'               => $this->status,
                'branchId'             => $this->branch_id,
            ];
        }
    }
