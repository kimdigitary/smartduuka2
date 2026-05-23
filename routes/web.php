<?php

    use Illuminate\Support\Facades\Route;

    foreach ( config( 'tenancy.central_domains' , [] ) as $domain ) {
        Route::domain( $domain )->group( function () {
            Route::get( '/q/{tenant}/{quotation}' , function (string $tenant , string $quotation) {
                return redirect( "https://{$tenant}.smartduuka.com/share/quotation/{$quotation}" );
            } );
            Route::get( '/opcache' , function () {
                return response()->json( opcache_get_status( FALSE ) );
            } );
        } );
    }
    Route::get( '/l' , function () {
        return [ 'Laravel' => app()->version() ];
    } );