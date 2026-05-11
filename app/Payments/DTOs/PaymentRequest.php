<?php

    namespace App\Payments\DTOs;

    readonly class PaymentRequest
    {
        public function __construct(
            public string $phone ,
            public float $amount ,
            public string $description ,
            public string $transactionId ,
            public string $notificationUrl ,
            public string $failureUrl = '' ,
            public array $meta = [] ,      // any gateway-specific extras
        ) {}
    }
