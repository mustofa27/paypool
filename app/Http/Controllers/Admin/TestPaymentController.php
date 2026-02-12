<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Exception;

class TestPaymentController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Show test payment form
     */
    public function create()
    {
        $apps = App::where('is_active', true)->get();
        return view('admin.test-payment', compact('apps'));
    }

    /**
     * Create a test payment with real Xendit integration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'app_id' => 'required|exists:apps,id',
            'amount' => 'required|numeric|min:10000',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        try {
            $app = App::findOrFail($validated['app_id']);
            $externalId = 'TEST-' . time() . '-' . rand(1000, 9999);

            // Create real Midtrans transaction
            $transactionData = $this->midtransService->createTransaction([
                'external_id' => $externalId,
                'amount' => $validated['amount'],
                'currency' => 'IDR',
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'description' => $validated['description'] ?? 'Test Payment',
            ]);

            // Create payment record
            $payment = Payment::create([
                'app_id' => $app->id,
                'external_id' => $externalId,
                'amount' => $validated['amount'],
                'currency' => 'IDR',
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'description' => $validated['description'] ?? 'Test Payment',
                'status' => 'pending',
                'midtrans_transaction_id' => $transactionData['transaction_id'] ?? $transactionData['order_id'] ?? null,
                'expired_at' => $transactionData['expiry_time'] ?? null,
                'metadata' => [
                    'is_test' => true,
                    'created_by_admin' => auth()->user()->name,
                ],
                'midtrans_response' => $transactionData,
            ]);

            $payment->logEvent('test_created', [
                'message' => 'Test payment created by admin with real Midtrans transaction',
                'admin' => auth()->user()->name,
                'payment_url' => $transactionData['redirect_url'] ?? $transactionData['payment_url'] ?? null,
            ]);

            // Redirect directly to Snap payment page if available
            $snapUrl = $transactionData['redirect_url'] ?? $transactionData['payment_url'] ?? null;
            if ($snapUrl) {
                return redirect()->away($snapUrl);
            }

            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('success', 'Test payment created successfully with real Midtrans transaction!')
                ->with('payment_url', $snapUrl);

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to create test payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Manually mark test payment as paid (for testing without completing Xendit payment)
     */
    public function markAsPaid(Payment $payment)
    {
        if (!$payment->isPending()) {
            return redirect()
                ->back()
                ->with('error', 'Only pending payments can be marked as paid');
        }

        $payment->markAsPaid([
            'payment_method' => 'TEST_PAYMENT',
            'payment_id' => 'test_payment_' . uniqid(),
        ]);

        $payment->logEvent('test_paid', [
            'message' => 'Test payment marked as paid by admin (manual)',
            'admin' => auth()->user()->name,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Payment marked as paid! Check if your webhook handler received the update.');
    }

    /**
     * Manually mark test payment as expired
     */
    public function markAsExpired(Payment $payment)
    {
        if (!$payment->isPending()) {
            return redirect()
                ->back()
                ->with('error', 'Only pending payments can be marked as expired');
        }

        $payment->markAsExpired();

        $payment->logEvent('test_expired', [
            'message' => 'Test payment marked as expired by admin (manual)',
            'admin' => auth()->user()->name,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Payment marked as expired! Check if your webhook handler received the update.');
    }
}
