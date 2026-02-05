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
            // Build request payload with only required/valid Xendit v2 fields
            $payload = [
                'external_id' => $data['external_id'],
                'amount' => (int)$data['amount'],
                'currency' => $data['currency'] ?? 'IDR',
                'description' => $data['description'] ?? 'Payment',
                'invoice_duration' => config('xendit.invoice.expiry_duration', 86400),
                'customer' => [
                    'given_names' => $data['customer_name'],
                    'email' => $data['customer_email'],
                ],
            ];

            // Add optional customer phone if provided
            if (!empty($data['customer_phone'])) {
                $payload['customer']['mobile_number'] = $data['customer_phone'];
            }

            // Add redirect URLs if provided
            if (!empty($data['success_redirect_url'])) {
                $payload['success_redirect_url'] = $data['success_redirect_url'];
            }
            if (!empty($data['failure_redirect_url'])) {
                $payload['failure_redirect_url'] = $data['failure_redirect_url'];
            }

            // Add items if provided
            if (!empty($data['items'])) {
                $payload['items'] = $data['items'];
            }

            Log::info('Xendit invoice request', ['payload' => $payload]);

            $response = Http::withBasicAuth($this->apiKey, '')
                ->post("{$this->baseUrl}/v2/invoices", $payload);

            if ($response->failed()) {
                Log::error('Xendit invoice creation failed', [
                    'response' => $response->json(),
                    'status' => $response->status(),
                    'request_body' => [
                        'external_id' => $data['external_id'],
                        'amount' => $data['amount'],
                        'payer_email' => $data['customer_email'],
                        'description' => $data['description'] ?? 'Payment',
                        'invoice_duration' => config('xendit.invoice.expiry_duration', 86400),
                        'currency' => $data['currency'] ?? config('xendit.invoice.default_currency', 'IDR'),
                    ],
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

        // Xendit uses simple token comparison for x-callback-token header
        return hash_equals($webhookToken, $signature);
    }
}
