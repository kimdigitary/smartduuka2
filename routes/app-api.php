<?php

    declare( strict_types = 1 );

    use App\Http\Controllers\BusinessController;
    use Illuminate\Support\Facades\Route;

    Route::middleware( [ 'api' , 'auth:sanctum' ] )->group( function () {
        Route::apiResource( 'businesses' , BusinessController::class )->names( 'app.businesses' );;
    } );
