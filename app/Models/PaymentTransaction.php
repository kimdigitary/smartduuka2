<?php

    namespace App\Models;

    use App\Enums\TransactionPaymentStatus;
    use Illuminate\Database\Eloquent\Model;

    class PaymentTransaction extends Model
    {
        protected $fillable = [
            'transaction_id' ,
            'status' ,
            'vendor_transaction_id' ,
            'payment_type' ,
            'amount' ,
            'phone' ,
            'card' ,
            'status_message' ,
            'currency' ,
        ];
        protected $casts    = [ 'status' => TransactionPaymentStatus::class ];
    }
