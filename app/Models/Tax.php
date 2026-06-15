<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy( [ BranchScope::class ] )]
class Tax extends Model
{
    protected $table = "taxes";
    protected $fillable = ['name', 'code', 'tax_rate', 'status', 'branch_id'];
    protected $casts = [
        'id'       => 'integer',
        'name'     => 'string',
        'code'     => 'string',
        'tax_rate' => 'string',
        'status'   => Status::class,
    ];

    public function productTaxes():HasMany
    {
        return $this->hasMany(ProductTax::class);
    }
}
