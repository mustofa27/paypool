<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WebPaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['app:id,name']);

        if ($request->filled('app_id')) {
            $query->where('app_id', $request->app_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('midtrans_environment')) {
            $query->where('midtrans_environment', $request->midtrans_environment);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('external_id', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $payments = $query->latest()->paginate(15);

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        $payment->load(['app', 'logs', 'webhookLogs']);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Update the stored Midtrans environment for a payment.
     */
    public function updateEnvironment(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'midtrans_environment' => ['required', Rule::in(['sandbox', 'production'])],
        ]);

        $payment->update([
            'midtrans_environment' => $validated['midtrans_environment'],
        ]);

        $payment->logEvent('environment_updated', [
            'midtrans_environment' => $validated['midtrans_environment'],
            'updated_by' => optional(auth()->user())->name,
        ]);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment environment updated successfully.');
    }
}
