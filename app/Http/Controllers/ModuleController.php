<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\StoreModuleRequest;
    use App\Http\Resources\SystemModuleResource;
    use App\Models\ModuleFeature;
    use App\Models\SystemModule;
    use Exception;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class ModuleController extends Controller
    {
        public function index()
        {
            return SystemModuleResource::collection( SystemModule::with( [ 'moduleCategory' , 'features.moduleFeature' ] )
                                                                 ->orderBy( 'id' )->get() );
        }

        public function update(StoreModuleRequest $request)
        {
            $validatedData = $request->validated();
            $modules       = json_decode( $validatedData[ 'modules' ] , TRUE );

            try {
                DB::transaction( function () use ($modules) {
                    foreach ( $modules as $moduleData ) {
                        $module = SystemModule::find( $moduleData[ 'id' ] );
                        if ( $module ) {
                            $module->update( [ 'enabled' => $moduleData[ 'enabled' ] ] );
                        }

                        if ( isset( $moduleData[ 'features' ] ) ) {
                            foreach ( $moduleData[ 'features' ] as $featureData ) {
                                $feature = ModuleFeature::find( $featureData[ 'id' ] );
                                if ( $feature ) {
                                    $feature->update( [ 'enabled' => $featureData[ 'enabled' ] ] );
                                }
                            }
                        }
                    }

                    return SystemModuleResource::collection( SystemModule::with( [ 'moduleCategory' , 'features.moduleFeature' ] )->get() );

                } );
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::error( $exception->getMessage() );
                return response()->json( [ 'message' => 'An error occurred while updating modules.' ] , 500 );
            }
        }
    }
