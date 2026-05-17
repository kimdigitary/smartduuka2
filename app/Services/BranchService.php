<?php

    namespace App\Services;


    use App\Http\Requests\BranchRequest;
    use App\Libraries\QueryExceptionLibrary;
    use App\Models\Branch;
    use App\Models\TenantBranch;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;
    use Smartisan\Settings\Facades\Settings;

    class BranchService
    {
        public function list(Request $request)
        {
            try {
                $page     = $request->integer( 'page' , 1 );
                $status   = $request->integer( 'status' , 10 );
                $per_page = $request->integer( 'per_page' , 10 );
                $query    = $request->string( 'query' , 10 );
                $query    = $request->string( 'query' , 10 );

                return TenantBranch::forTenant( tenantId() )
                                   ->paginate( perPage: $per_page , page: $page );

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function store(BranchRequest $request)
        {
            try {
                $branch = Branch::create( [
                    'name'    => $request->input( 'name' ) ,
                    'code'    => $request->input( 'code' ) ,
                    'manager' => $request->input( 'manager' ) ,
                    'phone'   => $request->input( 'phone' ) ,
                    'email'   => $request->input( 'email' ) ,
                    'status'  => $request->input( 'status' ) ,
                    'address' => $request->input( 'address' ) ,
                ] );
                activityLog( "Created Branch: $branch->name" );
                return $branch;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function update(BranchRequest $request , Branch $branch)
        {
            try {
                $branch = tap( $branch )->update( $request->validated() );
                activityLog( "Updated Branch: $branch->name" );
                return $branch;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function destroy(Branch $branch) : void
        {
            try {
                if ( Settings::group( 'site' )->get( "site_default_branch" ) != $branch->id ) {
                    $branch->delete();
                    activityLog( "Deleted Branch: $branch->name" );
                }
                else {
                    throw new Exception( "Default branch not deletable" , 422 );
                }
            } catch ( Exception $exception ) {
                // Log::info($exception->getMessage());
                // throw new Exception($exception->getMessage(), 422);
                Log::info( QueryExceptionLibrary::message( $exception ) );
                throw new Exception( QueryExceptionLibrary::message( $exception ) , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function show(Branch $branch) : Branch
        {
            try {
                return $branch;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
