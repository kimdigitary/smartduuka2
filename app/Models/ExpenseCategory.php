<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\Scope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

    #[ScopedBy( [ BranchScope::class ] )]
    class ExpenseCategory extends Model
    {
        use HasFactory , HasRecursiveRelationships;

        public    $timestamps = FALSE;
        protected $fillable   = [ 'name' , 'user_id' , 'parent_id' , 'status' , 'description' , 'branch_id' ];

        public function parent_category() : BelongsTo
        {
            return $this->belongsTo( ExpenseCategory::class , 'parent_id' );
        }

        public function expenses() : HasMany
        {
            return $this->hasMany( Expense::class , 'expense_category_id' , 'id' );
        }

        #[Scope]
        protected function branch(Builder $query , int $branch_id) : void
        {
            $query->where( 'branch_id' , $branch_id );
        }
    }
