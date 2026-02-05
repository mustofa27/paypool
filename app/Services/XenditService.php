<?php

namespace App\Services;

use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('xendit.api_key');
        $this->baseUrl = config('xendit.base_url');
    }

    /**
     * Create an invoice in Xendit
     */
    public function createInvoice(array $data): array
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->post("{$this->baseUrl}/v2/invoices", [
                    'external_id' => $data['external_id'],
                    'amount' => $data['amount'],
                    'payer_email' => $data['customer_email'],
                    'description' => $data['description'] ?? 'Payment',
                    'invoice_duration' => config('xendit.invoice.expiry_duration', 86400),
                    'currency' => $data['currency'] ?? config('xendit.invoice.default_currency', 'IDR'),
                    'success_redirect_url' => $data['success_redirect_url'] ?? config('xendit.success_redirect_url'),
                    'failure_redirect_url' => $data['failure_redirect_url'] ?? config('xendit.failure_redirect_url'),
                    'customer' => [
                        'given_names' => $data['customer_name'],
                        'email' => $data['customer_email'],
                        'mobile_number' => $data['customer_phone'] ?? null,
                    ],
                    'customer_notification_preference' => [
                        'invoice_created' => ['email'],
                        'invoice_reminder' => ['email'],
                        'invoice_paid' => ['email'],
                    ],
                    'items' => $data['items'] ?? [
                        [
                            'name' => $data['description'] ?? 'Payment',
                            'quantity' => 1,
                            'price' => $data['amount'],
                        ],
                    ],
                    'fees' => $data['fees'] ?? [],
                ]);

            if ($response->failed()) {
                Log::error('Xendit invoice creation failed', [
                    'response' => $response->json(),
                    'status' => $response->status(),
                ]);

                throw new Exception('Failed to create invoice: ' . ($response->json()['message'] ?? 'Unknown error'));
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Xendit invoice creation error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Get invoice details from Xendit
     */
    public function getInvoice(string $invoiceId): array
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->get("{$this->baseUrl}/v2/invoices/{$invoiceId}");

            if ($response->failed()) {
                throw new Exception('Failed to get invoice');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Xendit get invoice error', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceId,
            ]);

            throw $e;
        }
    }

    /**
     * Expire/cancel an invoice in Xendit
     */
    public function expireInvoice(string $invoiceId): array
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->post("{$this->baseUrl}/v2/invoices/{$invoiceId}/expire");

            if ($response->failed()) {
                throw new Exception('Failed to expire invoice');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Xendit expire invoice error', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceId,
            ]);

            throw $e;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookToken = config('xendit.webhook_token');
        
        if (!$webhookToken) {
            return false;
        }

        $calculatedSignature = hash_hmac('sha256', $payload, $webhookToken);
        
        return hash_equals($calculatedSignature, $signature);
    }
}
