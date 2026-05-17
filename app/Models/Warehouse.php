<?php

    namespace App\Models;

    use App\Enums\Status;
    use App\Http\Requests\PaginateRequest;
    use App\Models\Scopes\BranchScope;
    use App\Services\StockService;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    #[ScopedBy( [ BranchScope::class ] )]
    class Warehouse extends Model
    {
        use HasFactory;

        protected $fillable = [
            'name' ,
            'deletable' ,
            'email' ,
            'location' ,
            'phone' ,
            'manager' ,
            'capacity' ,
            'status' ,
            'id' ,
            'branch_id'
        ];
        protected $casts    = [
            'deletable' => 'boolean',
            'status'    => Status::class,
        ];

//        public function getStocksAttribute()
//        {
//            $stockService = new StockService();
//            $request      = new PaginateRequest();
//            $request->merge( [ 'warehouse_id' => $this->id ] );
//            return $stockService->list( $request );
//        }
    }
