<?php

namespace App\Payments\DTOs;

class WebhookPayload
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $status,
        public readonly string $gatewayRef,
        public readonly string $message,
        public readonly string $payerName,
        public readonly array  $raw = [],
    ) {}
}
