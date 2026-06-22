<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\PaymentServiceProvider;
use App\Providers\TenancyServiceProvider;
use IFRS\IFRSServiceProvider;
use Tymon\JWTAuth\Providers\LaravelServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    TenancyServiceProvider::class,
    PaymentServiceProvider::class,
    LaravelServiceProvider::class,
//    IFRSServiceProvider::class,
];
