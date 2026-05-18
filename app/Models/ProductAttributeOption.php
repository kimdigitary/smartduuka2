<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[ScopedBy( [ BranchScope::class ] )]
class ProductAttributeOption extends Model
{
    use HasFactory;
    protected $table = "product_attribute_options";
    protected $fillable = ['product_attribute_id', 'name'];

    public function productAttribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class);
    }
}
