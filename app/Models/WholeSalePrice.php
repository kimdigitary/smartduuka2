<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

#[ScopedBy([BranchScope::class])]
class WholeSalePrice extends Model
{
    public $timestamps = FALSE;
    protected $table = 'whole_sale_prices';

    protected $fillable = [
        'minQuantity',
        'price',
        'item_id',
        'item_type', 'batch',
        'branch_id', 'is_active'
    ];
    protected $casts = ['is_active' => Status::class];

    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', Status::ACTIVE);
    }
}
