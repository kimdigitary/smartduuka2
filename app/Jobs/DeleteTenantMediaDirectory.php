<?php

    namespace App\Jobs;

    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Support\Facades\File;

    class DeleteTenantMediaDirectory implements ShouldQueue
    {
        use Dispatchable , Queueable;

        public function __construct(public readonly mixed $tenant) {}

        public function handle() : void
        {
            $tenantId  = $this->tenant->getTenantKey();
            $suffix    = config( 'tenancy.filesystem.suffix_base' );
            $directory = storage_path( $suffix . $tenantId );
            File::deleteDirectory( $directory );
//            Storage::disk( 'public' )->deleteDirectory( $directory );
        }
    }