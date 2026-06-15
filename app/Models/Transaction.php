<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[ScopedBy( [ BranchScope::class ] )]
class Transaction extends Model
{
    protected $table = "transactions";
    protected $fillable = ['order_id', 'transaction_no', 'amount', 'payment_method', 'type', 'sign', 'branch_id'];
    protected $casts = [
        'id'             => 'integer',
        'order_id'       => 'integer',
        'transaction_no' => 'string',
        'amount'         => 'decimal:6',
        'payment_method' => 'string',
        'type'           => 'string',
        'sign'           => 'string',
    ];

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
