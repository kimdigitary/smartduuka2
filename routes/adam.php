<?php


    use App\Http\Controllers\Adam\AdamController;

    Route::post( 'pay' , [ AdamController::class , 'pay2' ] )->name( 'pay' );
    Route::post( 'success' , [ AdamController::class , 'success' ] )->name( 'success' );
    Route::post( 'jpesa-success' , [ AdamController::class , 'success' ] )->name( 'jpesa-success' );
    Route::get( 'vouchers' , [ AdamController::class , 'index' ] );
    Route::get( 'checkuseradded' , [ AdamController::class , 'checkUserAdded' ] );
    Route::get( 'routers' , [ AdamController::class , 'routers' ] )->name( 'routers' );
    Route::get( 'packages/{router}' , [ AdamController::class , 'packages' ] )->name( 'packages' );