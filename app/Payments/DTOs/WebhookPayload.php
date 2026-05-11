<?php

namespace App\Payments\DTOs;

class WebhookPayload
{
    public function __construct(
        public readonly string $transactionId,   // your internal reference
        public readonly string $status,          // 'success' | 'failed'
        public readonly string $gatewayRef,      // vendor's own transaction id
        public readonly string $message,
        public readonly string $payerName,
        public readonly array  $raw = [],
    ) {}
}
