<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\EmployeeRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\UserChangePasswordRequest;
    use App\Http\Resources\EmployeeResource;
    use App\Http\Resources\OrderResource;
    use App\Models\User;
    use App\Services\EmployeeService;
    use App\Services\OrderService;
    use App\Services\PinService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Routing\Attributes\Controllers\Middleware;

    #[Middleware( 'users.limit' , only: [ 'store' , 'update' ] )]
    class EmployeeController extends AdminController
    {
        private EmployeeService $employeeService;
        private OrderService    $orderService;

        public function __construct(EmployeeService $employeeService , OrderService $orderService)
        {
            parent::__construct();
            $this->employeeService = $employeeService;
            $this->orderService    = $orderService;
        }

        public function index(PaginateRequest $request)
        {
            try {
                return EmployeeResource::collection( $this->employeeService->list( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function store(EmployeeRequest $request , PinService $pin_service) : Response | EmployeeResource | Application | ResponseFactory
        {
            try {
                $user = $this->employeeService->store( $request , $pin_service );
                return new EmployeeResource( $user );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(EmployeeRequest $request , User $employee , PinService $pin_service) : Response | EmployeeResource | Application | ResponseFactory
        {
            try {
                $user = $this->employeeService->update( $request , $employee , $pin_service );

                return new EmployeeResource( $user );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function destroy(Request $request) : Response | Application | ResponseFactory
        {
            try {
                User::destroy( $request->ids );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function show(User $employee) : Response | EmployeeResource | Application | ResponseFactory
        {
            try {
                return new EmployeeResource( $this->employeeService->show( $employee ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

//    public function export(PaginateRequest $request): Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | Application | ResponseFactory
//    {
//        try {
//            return Excel::download(new EmployeeExport($this->employeeService, $request), 'Employee.xlsx');
//        } catch (Exception $exception) {
//            return response(['status' => false, 'message' => $exception->getMessage()], 422);
//        }
//    }

        public function changePassword(UserChangePasswordRequest $request , User $employee) : Response | EmployeeResource | Application | ResponseFactory
        {
            try {
                return new EmployeeResource( $this->employeeService->changePassword( $request , $employee ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function changeImage(ChangeImageRequest $request , User $employee) : Response | EmployeeResource | Application | ResponseFactory
        {
            try {
                return new EmployeeResource( $this->employeeService->changeImage( $request , $employee ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function myOrder(PaginateRequest $request , User $employee)
        {
            try {
                return OrderResource::collection( $this->orderService->userOrder( $request , $employee ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
