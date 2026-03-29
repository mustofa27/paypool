<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\WebhookLog;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentCallbackService
{
    /**
     * Dispatch payment.updated callback to app webhook URL and store webhook log.
     */
    public function dispatchPaymentUpdated(Payment $payment, array $context = [], ?string $eventType = null): void
    {
        $payment->loadMissing('app');

        if (!$payment->app || !$payment->app->webhook_url) {
            return;
        }

        $eventType = $eventType ?? ($context['event_type'] ?? 'payment.updated');

        $payload = [
            'event' => 'payment.updated',
            'payment' => [
                'external_id' => $payment->external_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'customer_name' => $payment->customer_name,
                'customer_email' => $payment->customer_email,
                'payment_method' => $payment->payment_method,
                'paid_at' => $payment->paid_at,
                'metadata' => $payment->metadata,
            ],
            'midtrans_data' => $context,
        ];

        $webhookLog = WebhookLog::create([
            'app_id' => $payment->app_id,
            'payment_id' => $payment->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'status' => 'success',
        ]);

        try {
            $response = Http::timeout(10)->post($payment->app->webhook_url, $payload);

            $webhookLog->update([
                'response' => [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ],
                'status' => $response->successful() ? 'success' : 'failed',
            ]);

            Log::info('Payment callback sent', [
                'payment_id' => $payment->id,
                'external_id' => $payment->external_id,
                'event_type' => $eventType,
                'status' => $response->status(),
            ]);
        } catch (Exception $e) {
            $webhookLog->update([
                'response' => ['error' => $e->getMessage()],
                'status' => 'failed',
            ]);

            Log::error('Payment callback failed', [
                'payment_id' => $payment->id,
                'external_id' => $payment->external_id,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
