<?php

use App\Helpers\TenantAwareUrlGenerator;
use Illuminate\Support\Facades\URL;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

uses( Tests\TestCase::class );

function tenantAwareMediaUrl(bool $assetHelperTenancy): string
{
    config( [
        'app.url'                                  => 'https://tenant.test' ,
        'tenancy.filesystem.asset_helper_tenancy' => $assetHelperTenancy ,
        'media-library.version_urls'              => FALSE ,
    ] );

    URL::forceRootUrl( 'https://tenant.test' );
    URL::forceScheme( 'https' );
    URL::useAssetOrigin( $assetHelperTenancy ? 'https://tenant.test/tenancy/assets' : NULL );

    $media            = new Media();
    $media->id        = 27;
    $media->file_name = 'smartduuka-new-logo.png';
    $media->disk      = 'public';

    $generator = ( new TenantAwareUrlGenerator( app( 'config' ) ) )
        ->setMedia( $media )
        ->setPathGenerator( new DefaultPathGenerator() );

    return $generator->getUrl();
}

it( 'uses documented asset helper urls when asset helper tenancy is enabled' , function () {
    expect( tenantAwareMediaUrl( TRUE ) )
        ->toBe( 'https://tenant.test/tenancy/assets/27/smartduuka-new-logo.png' );
} );

it( 'uses tenant asset urls when asset helper tenancy is disabled' , function () {
    expect( tenantAwareMediaUrl( FALSE ) )
        ->toBe( 'https://tenant.test/tenancy/assets/27/smartduuka-new-logo.png' );
} );
