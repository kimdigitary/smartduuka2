<?php

    return [
        App\Providers\AppServiceProvider::class ,
        App\Providers\FortifyServiceProvider::class ,
        App\Providers\TenancyServiceProvider::class ,
        App\Providers\PaymentServiceProvider::class ,
        Tymon\JWTAuth\Providers\LaravelServiceProvider::class ,
    ];
