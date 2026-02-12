<?php

namespace App\Services;

use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
	protected string $serverKey;
	protected string $baseUrl;

	public function __construct()
	{
		$this->serverKey = config('midtrans.server_key');
		$this->baseUrl = config('midtrans.base_url');
	}

	/**
	 * Create a transaction in Midtrans
	 */
	public function createTransaction(array $data): array
	{
		try {
			$payload = [
				'transaction_details' => [
					'order_id' => $data['external_id'],
					'gross_amount' => (int)$data['amount'],
				],
				'customer_details' => [
					'first_name' => $data['customer_name'],
					'email' => $data['customer_email'],
					'phone' => $data['customer_phone'] ?? null,
				],
				'item_details' => $data['items'] ?? [],
				'currency' => $data['currency'] ?? 'IDR',
				'expiry' => [
					'start_time' => now()->toIso8601String(),
					'duration' => config('midtrans.transaction.expiry_duration', 86400),
					'unit' => 'second',
				],
				'metadata' => $data['metadata'] ?? [],
			];

			// Add redirect URLs if provided
			if (!empty($data['success_redirect_url'])) {
				$payload['success_redirect_url'] = $data['success_redirect_url'];
			}
			if (!empty($data['failure_redirect_url'])) {
				$payload['failure_redirect_url'] = $data['failure_redirect_url'];
			}

			Log::info('Midtrans transaction request', ['payload' => $payload]);

			$response = Http::withBasicAuth($this->serverKey, '')
				->post("{$this->baseUrl}/v2/charge", $payload);

			if ($response->failed()) {
				Log::error('Midtrans transaction creation failed', [
					'response' => $response->json(),
					'status' => $response->status(),
				]);
				throw new Exception('Failed to create transaction: ' . ($response->json()['message'] ?? 'Unknown error'));
			}

			return $response->json();
		} catch (Exception $e) {
			Log::error('Midtrans transaction creation error', [
				'error' => $e->getMessage(),
				'data' => $data,
			]);
			throw $e;
		}
	}

	/**
	 * Get transaction status from Midtrans
	 */
	public function getTransactionStatus(string $orderId): array
	{
		try {
			$response = Http::withBasicAuth($this->serverKey, '')
				->get("{$this->baseUrl}/v2/{$orderId}/status");

			if ($response->failed()) {
				throw new Exception('Failed to get transaction status');
			}

			return $response->json();
		} catch (Exception $e) {
			Log::error('Midtrans get transaction status error', [
				'error' => $e->getMessage(),
				'order_id' => $orderId,
			]);
			throw $e;
		}
	}

	/**
	 * Cancel a transaction in Midtrans
	 */
	public function cancelTransaction(string $orderId): array
	{
		try {
			$response = Http::withBasicAuth($this->serverKey, '')
				->post("{$this->baseUrl}/v2/{$orderId}/cancel");

			if ($response->failed()) {
				throw new Exception('Failed to cancel transaction');
			}

			return $response->json();
		} catch (Exception $e) {
			Log::error('Midtrans cancel transaction error', [
				'error' => $e->getMessage(),
				'order_id' => $orderId,
			]);
			throw $e;
		}
	}

	/**
	 * Verify webhook signature (Midtrans uses HTTP Basic Auth or signature header)
	 */
	public function verifyWebhookSignature(string $payload, string $signature): bool
	{
		// Midtrans recommends validating signature/key or using server key for webhook
		$serverKey = config('midtrans.server_key');
		// Example: compare signature header or use hash_hmac if needed
		// For now, just check if signature matches server key (simple demo)
		return hash_equals($serverKey, $signature);
	}
}