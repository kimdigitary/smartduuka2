<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    #[ScopedBy( [ BranchScope::class ] )]
    class CreditDepositPurchase extends Model
    {
        use HasFactory;

        protected $fillable = [
            'order_id' ,
            'user_id' ,
            'type' ,
            'paid' ,
            'balance','date'
        ];

        public function paymentMethod() : HasOne
        {
            return $this->hasOne(PaymentMethod::class , 'id' , 'payment_method_id');
        }
    }
