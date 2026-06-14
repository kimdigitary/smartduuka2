<?php

    namespace App\Helpers;

    use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;


    class TenantAwareUrlGenerator extends DefaultUrlGenerator
    {
        public function getUrl() : string
        {
            $path = $this->getPathRelativeToRoot();
            $url  = config( 'tenancy.filesystem.asset_helper_tenancy' )
                ? asset( $path )
                : tenant_asset( $path );

            return $this->versionUrl( $url );
        }
    }
