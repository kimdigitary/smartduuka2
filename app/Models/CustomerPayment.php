<?php

    namespace App\Models;

    use App\Enums\CustomerPaymentType;
    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasOne;

    #[ScopedBy( [ BranchScope::class ] )]
    class CustomerPayment extends Model
    {
        use HasFactory;

        protected $guarded = [];
        protected $casts   = [
            'customer_payment_type' => CustomerPaymentType::class
        ];

        public function paymentMethod() : HasOne
        {
            return $this->hasOne( PaymentMethod::class , 'id' , 'payment_method_id' );
        }

        public function customer() : BelongsTo
        {
            return $this->belongsTo( User::class , 'user_id' );
        }
    }
