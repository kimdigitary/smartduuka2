<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    #[ScopedBy( [ BranchScope::class ] )]
    class ProductTag extends Model
    {
        protected $table    = "product_tags";
        protected $fillable = [ 'product_id' , 'name' , 'branch_id' ];
        protected $casts    = [
            'id'         => 'integer' ,
            'product_id' => 'integer' ,
            'name'       => 'string' ,
        ];

        public function product() : BelongsTo
        {
            return $this->belongsTo( Product::class , 'product_id' , 'id' );
        }
    }
