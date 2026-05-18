<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy( [ BranchScope::class ] )]
class ItemAttribute extends Model
{
    use HasFactory;

    protected $table = "item_attributes";
    protected $fillable = ['name', 'status'];
    protected $casts = [
        'id'     => 'integer',
        'name'   => 'string',
        'status' => Status::class,
    ];
}
