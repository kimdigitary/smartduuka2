<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    #[ScopedBy( [ BranchScope::class ] )]
    class PaymentMethodTransaction extends Model
    {
        protected $fillable = [
           'amount', 'charge', 'description', 'payment_method_id', 'item_id', 'item_type',
            'branch_id',
        ];

        public function paymentMethod() : BelongsTo
        {
            return $this->belongsTo( PaymentMethod::class );
        }
    }
