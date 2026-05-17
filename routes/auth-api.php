<?php

    declare( strict_types = 1 );

    use App\Http\Controllers\TenantBranchController;
    use Illuminate\Support\Facades\Route;

    Route::prefix( 'branches' )->name( 'branches.' )->group( function () {
        Route::apiResource( '/' , TenantBranchController::class )->except( 'destroy' );
        Route::delete( '/delete' , [ TenantBranchController::class , 'destroy' ] );
    } );

