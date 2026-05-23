<?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;

    class DeleteTenant extends Command
    {
        protected $signature = 'delete-tenant {id}';

        protected $description = 'Delete a tenant and its database.';

        public function handle() : void
        {
            $id = $this->argument( 'id' );

            $this->info( "Finding tenant {$id}..." );

            $tenant = Tenant::find( $id );

            if ( $tenant ) {
                $this->info( "Deleting tenant {$id}..." );
                $tenant->delete();
                $this->info( "Tenant {$id} deleted successfully." );

//                DB::delete( 'DELETE FROM tenant_branches CASCADE WHERE tenant_id = ?' , [ $id ] );

                DB::delete( 'DELETE FROM business_on_boards WHERE tenant = ?' , [ $id ] );

            }
            else {
                $this->error( "Tenant {$id} not found." );
            }

        }
    }
