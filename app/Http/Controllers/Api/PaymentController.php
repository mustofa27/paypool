<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Payment;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class PaymentController extends Controller
{
    protected XenditService $xenditService;

    public function __construct(XenditService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    /**
     * Create a new payment
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_id' => 'required|string|unique:payments,external_id',
            'amount' => 'required|numeric|min:10000', // Min 10,000 IDR
            'currency' => 'sometimes|string|size:3',
            'customer_name' => 'required|string|min:3|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'metadata' => 'nullable|array',
            'success_redirect_url' => 'nullable|url',
            'failure_redirect_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            /** @var App $app */
            $app = $request->input('authenticated_app');

            // Create payment record
            $payment = Payment::create([
                'app_id' => $app->id,
                'external_id' => $request->external_id,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'IDR',
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'description' => $request->description,
                'status' => 'pending',
                'metadata' => $request->metadata,
            ]);

            // Create invoice in Xendit
            $invoiceData = $this->xenditService->createInvoice([
                'external_id' => $request->external_id,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'IDR',
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'description' => $request->description ?? 'Payment',
                'success_redirect_url' => $request->success_redirect_url ?? $app->success_redirect_url,
                'failure_redirect_url' => $request->failure_redirect_url ?? $app->failure_redirect_url,
            ]);

            // Update payment with Xendit data
            $payment->update([
                'xendit_invoice_id' => $invoiceData['id'],
                'expired_at' => $invoiceData['expiry_date'],
                'xendit_response' => $invoiceData,
            ]);

            // Log the creation
            $payment->logEvent('created', [
                'invoice_id' => $invoiceData['id'],
                'invoice_url' => $invoiceData['invoice_url'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'external_id' => $payment->external_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'invoice_url' => $invoiceData['invoice_url'],
                    'expired_at' => $payment->expired_at,
                ],
            ], 201);
        } catch (Exception $e) {
            if (isset($payment)) {
                $payment->markAsFailed();
                $payment->logEvent('creation_failed', [
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment by external ID
     */
    public function show(Request $request, string $externalId)
    {
        /** @var App $app */
        $app = $request->input('authenticated_app');

        $payment = Payment::where('external_id', $externalId)
            ->where('app_id', $app->id)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'external_id' => $payment->external_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'customer_name' => $payment->customer_name,
                'customer_email' => $payment->customer_email,
                'payment_method' => $payment->payment_method,
                'paid_at' => $payment->paid_at,
                'expired_at' => $payment->expired_at,
                'metadata' => $payment->metadata,
                'created_at' => $payment->created_at,
            ],
        ]);
    }

    /**
     * List payments for authenticated app
     */
    public function index(Request $request)
    {
        /** @var App $app */
        $app = $request->input('authenticated_app');

        $query = Payment::where('app_id', $app->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $payments = $query->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Cancel a pending payment
     */
    public function cancel(Request $request, string $externalId)
    {
        /** @var App $app */
        $app = $request->input('authenticated_app');

        $payment = Payment::where('external_id', $externalId)
            ->where('app_id', $app->id)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        if (!$payment->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending payments can be cancelled',
            ], 400);
        }

        try {
            // Expire invoice in Xendit
            if ($payment->xendit_invoice_id) {
                $this->xenditService->expireInvoice($payment->xendit_invoice_id);
            }

            $payment->markAsExpired();
            $payment->logEvent('cancelled', [
                'cancelled_by' => 'app',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment cancelled successfully',
                'data' => [
                    'external_id' => $payment->external_id,
                    'status' => $payment->status,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel payment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
