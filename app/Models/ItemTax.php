<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    #[ScopedBy( [ BranchScope::class ] )]
    class ItemTax extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'item_id' ,
            'item_type' ,
            'tax_id' ,
            'branch_id' ,
        ];

        public function item() : MorphTo
        {
            return $this->morphTo();
        }

        public function tax() : BelongsTo
        {
            return $this->belongsTo( Tax::class );
        }
    }
