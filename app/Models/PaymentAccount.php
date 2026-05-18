<?php

    namespace App\Models;

    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    #[ScopedBy( [ BranchScope::class ] )]
    class PaymentAccount extends Model
    {
        use HasFactory;

        protected $guarded = [];
        public $timestamps = false;

        public function currency() : HasOne
        {
            return $this->hasOne(Currency::class, 'id', 'currency_id');
        }
    }
