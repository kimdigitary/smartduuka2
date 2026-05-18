<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    #[ScopedBy( [ BranchScope::class ] )]
    class OrderServiceAdon extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'order_service_product_id' ,
            'addon_id' ,
        ];

        public function orderServiceProduct() : BelongsTo
        {
            return $this->belongsTo( OrderServiceProduct::class );
        }

        public function addon() : BelongsTo
        {
            return $this->belongsTo( ServiceAddOn::class , 'addon_id' );
        }
    }
