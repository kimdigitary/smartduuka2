<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\Scope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    #[ScopedBy( [ BranchScope::class ] )]
    class PurchaseReturn extends Model
    {
        protected $fillable = [
            'supplier_id' ,
            'purchase_id' ,
            'date' ,
            'debit_note' ,
            'notes' ,
        ];

        public function supplier() : BelongsTo
        {
            return $this->belongsTo( Supplier::class );
        }

        public function purchase() : BelongsTo
        {
            return $this->belongsTo( Purchase::class );
        }

        protected function casts() : array
        {
            return [
                'date' => 'datetime' ,
            ];
        }

        #[Scope]
        protected function branch(Builder $query , int | string $branch_id) : void
        {
            $query->where( 'branch_id' , $branch_id );
        }
    }
