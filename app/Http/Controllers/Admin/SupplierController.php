<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\SupplierRequest;
    use App\Http\Resources\PurchaseResource;
    use App\Http\Resources\SupplierResource;
    use App\Models\Purchase;
    use App\Models\Supplier;
    use App\Services\SupplierService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Routing\Attributes\Controllers\Middleware;

    #[Middleware( 'feature' , only: [ 'store' ] )]
    class SupplierController extends AdminController
    {
        private SupplierService $supplierService;

        public function __construct(SupplierService $supplierService)
        {
            parent::__construct();
            $this->supplierService = $supplierService;
        }

        public function index(PaginateRequest $request)
        {
            try {
                return SupplierResource::collection( $this->supplierService->list( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function store(SupplierRequest $request)
        {
            try {
                return new SupplierResource( $this->supplierService->store( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(SupplierRequest $request , Supplier $supplier)
        {
            try {
                return new SupplierResource( $this->supplierService->update( $request , $supplier ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function destroy(Request $request) : Response | Application | ResponseFactory
        {
            try {
                Supplier::destroy( $request->ids );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }


        public function show(Supplier $supplier)
        {
            try {
                return new SupplierResource( $this->supplierService->show( $supplier ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function purchases(Supplier $supplier)
        {
            try {
                return PurchaseResource::collection( Purchase::where( 'supplier_id' , $supplier->id )
//                                                        ->where('status', PurchaseStatus::ORDERED)
                                                             ->get() );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
