<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    #[ScopedBy( [ BranchScope::class ] )]
    class OrderPaymentMethod extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'payment_method_id' ,
            'amount' ,
            'order_id' ,
            'branch_id',
        ];

        public function paymentMethod() : BelongsTo
        {
            return $this->belongsTo( PaymentMethod::class , 'payment_method_id' , 'id' );
        }
    }
