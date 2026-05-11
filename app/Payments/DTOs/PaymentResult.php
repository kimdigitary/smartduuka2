<?php

namespace App\Payments\DTOs;

class PaymentResult
{
    public function __construct(
        public readonly bool   $success,
        public readonly string $status,          // 'pending' | 'success' | 'failed'
        public readonly string $transactionId,
        public readonly string $message = '',
        public readonly array  $raw = [],
    ) {}

    public static function pending(string $transactionId, string $message = '', array $raw = []): self
    {
        return new self(
            success:       true,
            status:        'pending',
            transactionId: $transactionId,
            message:       $message,
            raw:           $raw,
        );
    }

    public static function failed(string $message = '', array $raw = []): self
    {
        return new self(
            success:       false,
            status:        'failed',
            transactionId: '',
            message:       $message,
            raw:           $raw,
        );
    }
}
