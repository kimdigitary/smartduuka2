<?php

    namespace App\Models;

    use App\Enums\CustomerWalletTransactionType;
    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    #[ScopedBy( [ BranchScope::class ] )]
    class CustomerWalletTransaction extends Model
    {

        protected $fillable = [
            'user_id' ,
            'amount' ,
            'payment_method_id' ,
            'reference' ,
            'type' ,
            'balance' ,
            'register_id' ,
            'branch_id',
        ];

        protected $casts = [ 'type' => CustomerWalletTransactionType::class ];

        public function user() : BelongsTo
        {
            return $this->belongsTo( User::class );
        }

        public function paymentMethod() : BelongsTo
        {
            return $this->belongsTo( PaymentMethod::class );
        }
    }
