<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    #[ScopedBy( [ BranchScope::class ] )]
    class ServiceItem extends Model
    {
        protected $fillable = [
            'item_id' ,
            'item_type' ,
            'quantity' ,
            'price_id' ,
            'price_type' ,
            'total' ,
            'service_id' ,
            'price' ,
            'branch_id',
        ];
        protected $casts    = [ 'price' => 'float' , 'quantity' => 'float' , 'total' => 'float' ];


        public function item() : MorphTo
        {
            return $this->morphTo();
        }

        public function product() : BelongsTo
        {
            return $this->belongsTo( Product::class , 'item_id' );
        }

        public function service() : BelongsTo
        {
            return $this->belongsTo( Service::class );
        }
    }
