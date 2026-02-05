<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Payment;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Exception;

class TestPaymentController extends Controller
{
    protected XenditService $xenditService;

    public function __construct(XenditService $xenditService)
    {
        $this->xenditService = $xenditService;
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

            // Create real Xendit invoice
            $invoiceData = $this->xenditService->createInvoice([
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
                'xendit_invoice_id' => $invoiceData['id'],
                'expired_at' => $invoiceData['expiry_date'],
                'metadata' => [
                    'is_test' => true,
                    'created_by_admin' => auth()->user()->name,
                ],
                'xendit_response' => $invoiceData,
            ]);

            $payment->logEvent('test_created', [
                'message' => 'Test payment created by admin with real Xendit invoice',
                'admin' => auth()->user()->name,
                'invoice_url' => $invoiceData['invoice_url'],
            ]);

            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('success', 'Test payment created successfully with real Xendit invoice!')
                ->with('payment_url', $invoiceData['invoice_url']);

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
