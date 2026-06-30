<?php

    namespace App\Http\Resources\Accounting;

    use IFRS\Models\Account;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Account */
    class AccountResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'          => $this->id,
                'name'        => $this->name,
                'code'        => (string) $this->code,
                'accountType' => $this->account_type,
                'categoryId'  => $this->category_id,
                'currencyId'  => $this->currency_id,
                'description' => $this->description,
                'isActive'    => (bool) ( $this->is_active ?? TRUE ),
                'isPettyCash' => (bool) $this->is_petty_cash,
                // Ledger balance on the account's normal side (injected by the controller).
                'balance'     => (float) ( $this->report_balance ?? 0 ),
                'partyRef'    => $this->party_type
                    ? [ 'type' => $this->party_type, 'externalId' => (string) $this->party_id ]
                    : NULL,
            ];
        }
    }
