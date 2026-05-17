<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    #[ScopedBy( [ BranchScope::class ] )]
    class RetailPrice extends Model
    {
        use HasFactory;

        public    $timestamps = FALSE;
        protected $guarded    = [];
        protected $casts      = [
            'price' => 'decimal'
        ];

        public function item() : MorphTo
        {
            return $this->morphTo();
        }

        public function unit() : BelongsTo
        {
            return $this->belongsTo( Unit::class , 'unit_id' , 'id' );
        }
    }
