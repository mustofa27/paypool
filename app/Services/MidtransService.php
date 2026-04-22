<?php

namespace App\Services;

use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
	/**
	 * Resolve and validate the Midtrans environment config.
	 */
	protected function getEnvironmentConfig(?string $environment = null): array
	{
		$resolvedEnvironment = $environment ?: config('midtrans.default_environment', 'sandbox');
		if (!in_array($resolvedEnvironment, ['sandbox', 'production'], true)) {
			$resolvedEnvironment = 'sandbox';
		}

		$config = config("midtrans.environments.{$resolvedEnvironment}", []);
		$serverKey = $config['server_key'] ?? null;
		$snapBaseUrl = isset($config['snap_base_url']) ? rtrim($config['snap_base_url'], '/') : null;
		$apiBaseUrl = isset($config['api_base_url']) ? rtrim($config['api_base_url'], '/') : null;

		if (empty($serverKey) || empty($snapBaseUrl) || empty($apiBaseUrl)) {
			throw new Exception("Midtrans configuration for '{$resolvedEnvironment}' is incomplete.");
		}

		return [
			'environment' => $resolvedEnvironment,
			'server_key' => $serverKey,
			'snap_base_url' => $snapBaseUrl,
			'api_base_url' => $apiBaseUrl,
		];
	}

	/**
	 * Create a transaction in Midtrans
	 */
	public function createTransaction(array $data, ?string $environment = null): array
	{
		try {
			$environmentConfig = $this->getEnvironmentConfig($environment);
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
					'start_time' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s O'),
					'duration' => config('midtrans.transaction.expiry_duration', 86400),
					'unit' => 'second',
				],
			];
			// Only include metadata if it's a non-empty associative array
			if (!empty($data['metadata']) && is_array($data['metadata']) && array_keys($data['metadata']) !== range(0, count($data['metadata']) - 1)) {
				$payload['metadata'] = $data['metadata'];
			}
			// Add Snap callbacks for finish, error, unfinish if provided
			$callbacks = [];
			if (!empty($data['success_redirect_url'])) {
				$callbacks['finish'] = $data['success_redirect_url'];
			}
			if (!empty($data['failure_redirect_url'])) {
				$callbacks['error'] = $data['failure_redirect_url'];
			}
			if (!empty($data['unfinish_redirect_url'])) {
				$callbacks['unfinish'] = $data['unfinish_redirect_url'];
			}
			if (!empty($callbacks)) {
				$payload['callbacks'] = $callbacks;
			}

			// Add redirect URLs if provided (Snap supports finish/cancel callbacks)
			if (!empty($data['success_redirect_url'])) {
				$payload['callbacks']['finish'] = $data['success_redirect_url'];
			} elseif (!empty(config('midtrans.redirects.success_url'))) {
				$payload['callbacks']['finish'] = config('midtrans.redirects.success_url');
			}
			if (!empty($data['failure_redirect_url'])) {
				$payload['callbacks']['error'] = $data['failure_redirect_url'];
			} elseif (!empty(config('midtrans.redirects.failure_url'))) {
				$payload['callbacks']['error'] = config('midtrans.redirects.failure_url');
			}

			Log::info('Midtrans Snap transaction request', [
				'environment' => $environmentConfig['environment'],
				'payload' => $payload,
			]);

			$response = Http::withBasicAuth($environmentConfig['server_key'], '')
				->post("{$environmentConfig['snap_base_url']}/v1/transactions", $payload);

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
				'environment' => $environment,
				'data' => $data,
			]);
			throw $e;
		}
	}

	/**
	 * Get transaction status from Midtrans
	 */
	public function getTransactionStatus(string $orderId, ?string $environment = null): array
	{
		try {
			$environmentConfig = $this->getEnvironmentConfig($environment);
			$response = Http::withBasicAuth($environmentConfig['server_key'], '')
				->get("{$environmentConfig['api_base_url']}/v2/{$orderId}/status");

			if ($response->failed()) {
				throw new Exception('Failed to get transaction status');
			}

			return $response->json();
		} catch (Exception $e) {
			Log::error('Midtrans get transaction status error', [
				'error' => $e->getMessage(),
				'environment' => $environment,
				'order_id' => $orderId,
			]);
			throw $e;
		}
	}

	/**
	 * Cancel a transaction in Midtrans
	 */
	public function cancelTransaction(string $orderId, ?string $environment = null): array
	{
		try {
			$environmentConfig = $this->getEnvironmentConfig($environment);
			$response = Http::withBasicAuth($environmentConfig['server_key'], '')
				->post("{$environmentConfig['api_base_url']}/v2/{$orderId}/cancel");

			if ($response->failed()) {
				throw new Exception('Failed to cancel transaction');
			}

			return $response->json();
		} catch (Exception $e) {
			Log::error('Midtrans cancel transaction error', [
				'error' => $e->getMessage(),
				'environment' => $environment,
				'order_id' => $orderId,
			]);
			throw $e;
		}
	}

	/**
	 * Verify webhook signature (Midtrans uses HTTP Basic Auth or signature header)
	 */
	public function verifyWebhookSignature(string $payload, string $signature, ?string $environment = null): bool
	{
		// Midtrans recommends validating signature/key or using server key for webhook
		$environmentConfig = $this->getEnvironmentConfig($environment);
		$serverKey = $environmentConfig['server_key'];
		// Example: compare signature header or use hash_hmac if needed
		// For now, just check if signature matches server key (simple demo)
		return hash_equals($serverKey, $signature);
	}
}