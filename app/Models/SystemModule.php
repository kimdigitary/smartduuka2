<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class SystemModule extends Model
    {
        protected $fillable = [
            'name' ,
            'description' ,
            'icon' ,
            'price' ,
            'module_category_id' ,
        ];

        // Removed the casts() method for 'enabled' as it no longer exists on this table

        public function moduleCategory() : BelongsTo
        {
            return $this->belongsTo( ModuleCategory::class );
        }

        public function features() : HasMany
        {
            return $this->hasMany( ModuleFeature::class );
        }
    }