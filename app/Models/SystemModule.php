<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemModule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'module_category_id',
        'enabled',
    ];
    protected function casts() : array
    {
        return [
            'enabled' => 'boolean' ,
        ];
    }

    public function moduleCategory(): BelongsTo
    {
        return $this->belongsTo(ModuleCategory::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(SystemModuleFeature::class);
    }

}
