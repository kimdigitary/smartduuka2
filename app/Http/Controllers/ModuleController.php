<?php

    namespace App\Http\Controllers;

    use App\Enums\SystemPaymentType;
    use App\Http\Requests\StoreModuleRequest;
    use App\Http\Resources\SystemModuleResource;
    use App\Jobs\InitiatePaymentJob;
    use App\Models\BranchModule;
    use App\Models\PaymentTransaction;
    use App\Models\SystemModule;
    use Exception;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Smartisan\Settings\Facades\Settings;

    class ModuleController extends Controller
    {
        public function index()
        {
            $modules = $this->getModulesForBranch();

            return SystemModuleResource::collection( $modules );
        }

        public function update(StoreModuleRequest $request)
        {
            $validatedData = $request->validated();
            $branchId      = branchId();
            $modules       = json_decode( $validatedData[ 'modules' ] , TRUE );

            // Fetch the current state of branch modules to compare against.
            $existingBranchModules = BranchModule::where( 'branch_id' , $branchId )
                                                 ->whereIn( 'system_module_id' , array_column( $modules , 'id' ) )
                                                 ->get()->keyBy( 'system_module_id' );

            $amount        = 0;
            $idsForPayment = [];

            // Determine which modules require payment.
            foreach ( $modules as $moduleData ) {
                $isPaidModule     = isset( $moduleData[ 'price' ] ) && $moduleData[ 'price' ] > 0;
                $isBeingEnabled   = $moduleData[ 'enabled' ];
                $moduleId         = $moduleData[ 'id' ];
                $existingModule   = $existingBranchModules->get( $moduleId );
                $isAlreadyEnabled = $existingModule && $existingModule->enabled;

                if ( $isPaidModule && $isBeingEnabled && ! $isAlreadyEnabled ) {
                    $amount          += $moduleData[ 'price' ];
                    $idsForPayment[] = $moduleId;
                }
            }

            try {
                DB::transaction( function () use ($branchId , $modules , $idsForPayment) {
                    foreach ( $modules as $moduleData ) {
                        $moduleId     = $moduleData[ 'id' ];
                        $enabledState = $moduleData[ 'enabled' ];

                        // If the module is pending payment, ensure it is disabled until payment is confirmed.
                        if ( in_array( $moduleId , $idsForPayment ) ) {
                            $enabledState = FALSE;
                        }

                        DB::table( 'branch_modules' )->updateOrInsert(
                            [
                                'branch_id'        => $branchId ,
                                'system_module_id' => $moduleId
                            ] ,
                            [
                                'enabled'    => $enabledState ,
                                'updated_at' => now()
                            ]
                        );

                        if ( ! empty( $moduleData[ 'features' ] ) ) {
                            foreach ( $moduleData[ 'features' ] as $featureData ) {
                                DB::table( 'branch_module_feature' )->updateOrInsert(
                                    [
                                        'branch_id'         => $branchId ,
                                        'module_feature_id' => $featureData[ 'id' ]
                                    ] ,
                                    [
                                        'enabled'    => $featureData[ 'enabled' ] ,
                                        'updated_at' => now() ,
                                    ]
                                );
                            }
                        }
                    }
                } );

                if ( count( $idsForPayment ) > 0 ) {
                    $payment = json_decode( $validatedData[ 'payment' ] , TRUE );

                    centralContext( function () use ($payment , $amount , $idsForPayment) {
                        $transaction = PaymentTransaction::create( [
                            'amount'           => $amount ,
                            'phone'            => $payment[ 'phone' ] ,
                            'data'             => [
                                'email'         => auth()->user()->email ,
                                'business_name' => data_get( Settings::group( 'company' )->get()  , 'company_name' )
                            ] ,
                            'payment_type'     => SystemPaymentType::MODULE ,
                            'payment_type_id'  => json_encode( $idsForPayment ) ,
                            'tenant_branch_id' => branchId() ,
                            'tenant_id'        => tenantId() ,
                        ] );

                        InitiatePaymentJob::dispatch( $transaction );
                    } );
                }

                return response()->json();

            } catch ( Exception $exception ) {
                Log::error( $exception->getMessage() );
                return response()->json( [ 'message' => 'An error occurred while updating modules.' ] , 500 );
            }
        }

        private function getModulesForBranch()
        {
            $branchId = branchId();

            $modules = SystemModule::with( [ 'moduleCategory' , 'features' ] )->orderBy( 'id' )->get();

            $branchModules  = BranchModule::where( 'branch_id' , $branchId )->get()->keyBy( 'system_module_id' );
            $branchFeatures = DB::table( 'branch_module_feature' )->where( 'branch_id' , $branchId )->get()->keyBy( 'module_feature_id' );

            $modules->each( function ($module) use ($branchModules , $branchFeatures) {

                $branchModule    = $branchModules->get( $module->id );
                $module->enabled = $branchModule ? (bool) $branchModule->enabled : FALSE;

                $module->features->each( function ($feature) use ($branchFeatures) {
                    $branchFeature    = $branchFeatures->get( $feature->id );
                    $feature->enabled = $branchFeature ? (bool) $branchFeature->enabled : FALSE;
                } );

            } );

            return $modules;
        }
    }
