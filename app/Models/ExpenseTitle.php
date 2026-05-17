<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;

    #[ScopedBy( [ BranchScope::class ] )]
    class ExpenseTitle extends Model
    {
        use SoftDeletes;

        protected $fillable = [
            'name' ,
            'branch_id' ,
        ];
    }
