<?php

    namespace App\Models;

    use App\Enums\Department;
    use App\Enums\Priority;
    use App\Enums\PurchaseRequestStatus;
    use App\Models\Scopes\BranchScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphMany;

    #[ScopedBy( [ BranchScope::class ] )]
    class StockPurchaseRequest extends Model
    {
        protected $fillable = [
            'requester_name' ,
            'department' ,
            'priority' ,
            'date' ,
            'reason' ,
            'reference' ,
            'status' ,
            'supplier_id' ,
            'branch_id'
        ];
        protected $table    = 'stock_purchase_requests';

        protected function casts() : array
        {
            return [
                'date'       => 'datetime' ,
                'department' => Department::class ,
                'priority'   => Priority::class ,
                'status'     => PurchaseRequestStatus::class ,
            ];
        }

        public function stocks() : morphMany
        {
            return $this->morphMany( Stock::class , 'model' );
        }
    }
