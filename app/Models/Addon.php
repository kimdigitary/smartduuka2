<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy( [ BranchScope::class ] )]
class Addon extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id'];
}
