<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    #[ScopedBy( [ BranchScope::class ] )]
    class CustomerLedger extends Model
    {
        protected $fillable = [
            'date' ,
            'reference' ,
            'description' ,
            'bill_amount' ,
            'paid' ,
            'balance' ,
            'user_id' ,
        ];

        protected function casts() : array
        {
            return [
                'date' => 'datetime' ,
            ];
        }
    }
