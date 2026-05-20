<?php

    namespace App\Http\Resources;

    use App\Models\PaymentTransaction;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin PaymentTransaction */
    class PaymentTransactionResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                    => $this->id ,
                'transaction_id'        => $this->transaction_id ,
                'status'                => $this->status ,
                'vendor_transaction_id' => $this->vendor_transaction_id ,
                'payment_type'          => $this->payment_type ,
                'amount'                => $this->amount ,
                'phone'                 => $this->phone ,
                'card'                  => $this->card ,
                'status_message'        => $this->status_message ,
                'currency'              => $this->currency ,
                'created_at'            => $this->created_at ,
                'updated_at'            => $this->updated_at ,
            ];
        }
    }
