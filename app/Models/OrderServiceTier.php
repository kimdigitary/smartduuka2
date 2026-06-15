<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    #[ScopedBy( [ BranchScope::class ] )]
    class OrderServiceTier extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'order_service_product_id' ,
            'service_tier_id' ,
            'branch_id',
        ];

        public function orderServiceProduct() : BelongsTo
        {
            return $this->belongsTo( OrderServiceProduct::class );
        }

        public function serviceTier() : BelongsTo
        {
            return $this->belongsTo( ServiceTier::class );
        }
    }
