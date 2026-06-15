<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
#[ScopedBy( [ BranchScope::class ] )]
class ProductAttribute extends Model
{
    use HasFactory;
    protected $table = "product_attributes";
    protected $fillable = ['name','status', 'branch_id'];
    protected $casts = [
        'id'     => 'integer',
        'name'   => 'string',
        'status' => Status::class,
    ];

    public function productAttributeOptions(): HasMany
    {
        return $this->hasMany(ProductAttributeOption::class, 'product_attribute_id', 'id');
    }
}
