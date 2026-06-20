<?php

namespace App\Payments\Gateways;

use App\Payments\Contracts\PaymentGateway;
use App\Payments\DTOs\PaymentRequest;
use App\Payments\DTOs\PaymentResult;
use App\Payments\DTOs\WebhookPayload;
use App\YoPayments\YoAPI;
use Illuminate\Http\Request;

class YoUgandaGateway implements PaymentGateway
{
    public function charge(PaymentRequest $payment): PaymentResult
    {
        $yoAPI = $this->makeClient();

        $yoAPI->set_external_reference($payment->transactionId);
        $yoAPI->set_nonblocking('TRUE');
        $yoAPI->set_instant_notification_url($payment->notificationUrl);
        //            $yoAPI->set_failure_notification_url( $payment->failureUrl ?: $payment->notificationUrl );
        $yoAPI->set_failure_notification_url($payment->notificationUrl);

        $response = $yoAPI->ac_deposit_funds(
            normalisePhone($payment->phone),
            $payment->amount,
            $payment->description,
        );

        if (($response['Status'] ?? '') === 'OK') {
            return PaymentResult::pending(
                transactionId: $payment->transactionId,
                message: 'Payment request sent',
                raw: $response,
            );
        }

        return PaymentResult::failed(
            message: $response['StatusMessage'] ?? 'Yo! Uganda collection failed',
            raw: $response,
        );
    }

    /**
     * Submit a bank deposit request: notifies Yo! Payments that a user has
     * manually transferred funds into Yo!'s bank account, along with proof
     * of the transfer, so the funds can be credited into the wallet.
     *
     * @param  string  $transactionId  your internal transaction/tracking id
     * @param  float  $amount  the total amount deposited in the bank
     * @param  string  $narrative  description of the bank deposit
     * @param  string  $bankTransferFilePath  path to proof of transfer (e.g. scanned deposit slip), max 4MB
     * @param  array  $amountBreakdown  [['currency_code' => string, 'amount' => float], ...]
     */
    public function submitBankDeposit(
        string $transactionId,
        float $amount,
        string $narrative,
        string $bankTransferFilePath,
        array $amountBreakdown,
    ): PaymentResult {
        $yoAPI = $this->makeClient();

        $response = $yoAPI->ac_submit_bank_deposit(
            $amount,
            $narrative,
            $bankTransferFilePath,
            $amountBreakdown,
            $transactionId,
        );

        if (($response['Status'] ?? '') === 'OK') {
            return PaymentResult::pending(
                transactionId: $response['BankDepositRequestReference'] ?? $transactionId,
                message: 'Bank deposit request submitted',
                raw: $response,
            );
        }

        return PaymentResult::failed(
            message: $response['StatusMessage'] ?? 'Yo! Uganda bank deposit submission failed',
            raw: $response,
        );
    }

    /**
     * Check the status of a previously submitted bank deposit request.
     *
     * @param  string  $bankDepositRequestReference  the reference returned by submitBankDeposit
     */
    public function checkBankDepositStatus(string $bankDepositRequestReference): PaymentResult
    {
        $yoAPI = $this->makeClient();

        $response = $yoAPI->ac_bank_deposit_check_status(
            bank_deposit_request_reference: $bankDepositRequestReference,
        );

        if (($response['Status'] ?? '') !== 'OK') {
            return PaymentResult::failed(
                message: $response['StatusMessage'] ?? 'Yo! Uganda bank deposit status check failed',
                raw: $response,
            );
        }

        return match ($response['ProcessingStatus'] ?? '') {
            'PROCESSED', 'PROCESSED WITH MODIFICATION' => PaymentResult::success(
                transactionId: $bankDepositRequestReference,
                message: 'Bank deposit processed',
                raw: $response,
            ),
            'DECLINED' => PaymentResult::failed(
                message: 'Bank deposit request was declined',
                raw: $response,
            ),
            default => PaymentResult::pending(
                transactionId: $bankDepositRequestReference,
                message: 'Bank deposit request is still pending',
                raw: $response,
            ),
        };
    }

    public function isSuccessWebhook(Request $request): bool
    {
        return new YoAPI(
            username: config('payments.yo.username'),
            password: config('payments.yo.password'),
            mode: $this->mode(),
        )->receive_payment_notification($request);
    }

    public function isFailureWebhook(Request $request): bool
    {
        return new YoAPI(
            username: config('payments.yo.username'),
            password: config('payments.yo.password'),
            mode: $this->mode(),
        )->receive_payment_failure_notification($request);
    }

    public function parseWebhook(Request $request): WebhookPayload
    {
        $isSuccess = $this->isSuccessWebhook($request);

        return new WebhookPayload(
            transactionId: $isSuccess
                ? ($request->external_ref ?? '')
                : ($request->failed_transaction_reference ?? ''),
            status: $isSuccess ? 'success' : 'failed',
            gatewayRef: $request->network_ref ?? '',
            message: $request->StatusMessage ?? '',
            payerName: $request->payer_names ?? '',
            raw: $request->all(),
        );
    }

    private function makeClient(): YoAPI
    {
        return new YoAPI(
            username: config('payments.yo.username'),
            password: config('payments.yo.password'),
            mode: $this->mode(),
        );
    }

    private function mode(): string
    {
        return app()->isLocal() ? 'sandbox' : 'production';
    }
}
