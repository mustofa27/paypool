<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\WebhookLog;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WebhookController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Handle Midtrans webhook
     */
    public function midtrans(Request $request)
    {
        try {
            // Optionally: You can check HTTP Basic Auth here if you want extra security

            $data = $request->all();
            // Support both 'external_id' and 'order_id' for compatibility
            $externalId = $data['external_id'] ?? $data['order_id'] ?? null;

            if (!$externalId) {
                Log::warning('Midtrans webhook missing external_id/order_id', $data);
                return response()->json(['success' => false], 400);
            }

            // Find payment
            $payment = Payment::where('external_id', $externalId)->first();

            if (!$payment) {
                Log::warning('Payment not found for webhook', [
                    'external_id' => $externalId,
                ]);
                return response()->json(['success' => false], 404);
            }

            // Log webhook
            $webhookLog = WebhookLog::create([
                'app_id' => $payment->app_id,
                'payment_id' => $payment->id,
                'event_type' => $data['status'] ?? 'unknown',
                'payload' => $data,
                'status' => 'success',
            ]);

            // Process webhook based on status
            $this->processWebhook($payment, $data);

            // Forward webhook to app
            if ($payment->app->webhook_url) {
                $this->forwardWebhook($payment, $data, $webhookLog);
            }

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            Log::error('Midtrans webhook processing error', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Process webhook data
     */
    protected function processWebhook(Payment $payment, array $data): void
    {
        $status = strtolower($data['transaction_status'] ?? $data['status'] ?? '');

        switch ($status) {
            case 'paid':
            case 'settled':
                if (!$payment->isPaid()) {
                    $payment->markAsPaid([
                        'payment_method' => $data['payment_method'] ?? null,
                        'payment_id' => $data['payment_id'] ?? $data['id'] ?? null,
                    ]);

                    $payment->logEvent('paid', $data);

                    Log::info('Payment marked as paid', [
                        'payment_id' => $payment->id,
                        'external_id' => $payment->external_id,
                    ]);
                }
                break;

            case 'expired':
                if ($payment->isPending()) {
                    $payment->markAsExpired();
                    $payment->logEvent('expired', $data);

                    Log::info('Payment marked as expired', [
                        'payment_id' => $payment->id,
                        'external_id' => $payment->external_id,
                    ]);
                }
                break;

            case 'failed':
                $payment->markAsFailed();
                $payment->logEvent('failed', $data);

                Log::info('Payment marked as failed', [
                    'payment_id' => $payment->id,
                    'external_id' => $payment->external_id,
                ]);
                break;

            default:
                $payment->logEvent($status, $data);
                Log::info('Payment webhook received', [
                    'payment_id' => $payment->id,
                    'status' => $status,
                ]);
                break;
        }

        // Update midtrans_response with latest data
        $payment->update([
            'midtrans_response' => array_merge($payment->midtrans_response ?? [], $data),
        ]);
    }

    /**
     * Forward webhook to app's callback URL
     */
    protected function forwardWebhook(Payment $payment, array $data, WebhookLog $webhookLog): void
    {
        try {
            $response = Http::timeout(10)
                ->post($payment->app->webhook_url, [
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
                    'midtrans_data' => $data,
                ]);

            $webhookLog->update([
                'response' => [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ],
                'status' => $response->successful() ? 'success' : 'failed',
            ]);

            Log::info('Webhook forwarded to app', [
                'app_id' => $payment->app_id,
                'webhook_url' => $payment->app->webhook_url,
                'status' => $response->status(),
            ]);
        } catch (Exception $e) {
            $webhookLog->update([
                'response' => ['error' => $e->getMessage()],
                'status' => 'failed',
            ]);

            Log::error('Failed to forward webhook to app', [
                'app_id' => $payment->app_id,
                'webhook_url' => $payment->app->webhook_url,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
