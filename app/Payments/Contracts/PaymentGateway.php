<?php

    namespace App\Payments\Contracts;

    use App\Payments\DTOs\PaymentRequest;
    use App\Payments\DTOs\PaymentResult;
    use App\Payments\DTOs\WebhookPayload;
    use Illuminate\Http\Request;

    interface PaymentGateway
    {
        public function charge(PaymentRequest $payment) : PaymentResult;

        public function isSuccessWebhook(Request $request) : bool;

        public function isFailureWebhook(Request $request) : bool;

        public function parseWebhook(Request $request) : WebhookPayload;
    }
