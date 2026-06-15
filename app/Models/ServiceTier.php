<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    #[ScopedBy( [ BranchScope::class ] )]
    class ServiceTier extends Model
    {
        protected $fillable = [
            'name' ,
            'price' ,
            'features' ,
            'service_id' ,
            'branch_id',
        ];

        protected $casts = ['price' => 'float'];

        public function service() : BelongsTo
        {
            return $this->belongsTo( Service::class );
        }
    }
