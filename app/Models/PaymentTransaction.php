<?php

    namespace App\Models;

    use App\Enums\SystemPaymentType;
    use App\Enums\TransactionPaymentStatus;
    use Illuminate\Database\Eloquent\Model;

    class PaymentTransaction extends Model
    {
        protected $fillable = [
            'transaction_id' ,
            'status' ,
            'vendor_transaction_id' ,
            'payment_type' , 'payment_type_id' ,
            'amount' ,
            'phone' ,
            'card' ,
            'status_message' ,
            'currency' , 'tenant_branch_id' , 'tenant_id' , 'data'
        ];

        protected $casts = [ 'status' => TransactionPaymentStatus::class , 'payment_type' => SystemPaymentType::class , 'data' => 'array' ];
    }
