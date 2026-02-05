@extends('layouts.app')

@section('title', 'Payment Details - PayPool Admin')

@section('content')
<div class="px-4 sm:px-0">
    <div class="mb-6">
        <a href="{{ route('admin.payments.index') }}" class="text-indigo-600 hover:text-indigo-900">
            <i class="fas fa-arrow-left mr-2"></i>Back to Payments
        </a>
    </div>

    <!-- Payment Details -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $payment->external_id }}</h2>
                <p class="text-gray-600 mt-2">{{ $payment->description }}</p>
                @if($payment->metadata && isset($payment->metadata['is_test']) && $payment->metadata['is_test'])
                <span class="inline-block mt-2 px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                    <i class="fas fa-flask"></i> Test Payment
                </span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full 
                    @if($payment->status == 'paid') bg-green-100 text-green-800
                    @elseif($payment->status == 'pending') bg-yellow-100 text-yellow-800
                    @elseif($payment->status == 'expired') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($payment->status) }}
                </span>
                
                @if($payment->status == 'pending')
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('admin.payments.mark-paid', $payment) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Mark this payment as PAID?')"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 px-3 rounded">
                            <i class="fas fa-check"></i> Mark Paid
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.payments.mark-expired', $payment) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Mark this payment as EXPIRED?')"
                                class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold py-2 px-3 rounded">
                            <i class="fas fa-times"></i> Mark Expired
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">App</h3>
                <a href="{{ route('admin.apps.show', $payment->app) }}" class="text-indigo-600 hover:text-indigo-900">
                    {{ $payment->app->name }}
                </a>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Amount</h3>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($payment->amount, 0) }} {{ $payment->currency }}</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Customer Name</h3>
                <p class="text-gray-900">{{ $payment->customer_name }}</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Customer Email</h3>
                <p class="text-gray-900">{{ $payment->customer_email }}</p>
            </div>

            @if($payment->customer_phone)
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Customer Phone</h3>
                <p class="text-gray-900">{{ $payment->customer_phone }}</p>
            </div>
            @endif

            @if($payment->payment_method)
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Payment Method</h3>
                <p class="text-gray-900">{{ $payment->payment_method }}</p>
            </div>
            @endif

            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Created At</h3>
                <p class="text-gray-900">{{ $payment->created_at->format('M d, Y H:i:s') }}</p>
            </div>

            @if($payment->paid_at)
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Paid At</h3>
                <p class="text-gray-900">{{ $payment->paid_at->format('M d, Y H:i:s') }}</p>
            </div>
            @endif

            @if($payment->expired_at)
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Expires At</h3>
                <p class="text-gray-900">{{ $payment->expired_at->format('M d, Y H:i:s') }}</p>
            </div>
            @endif

            @if($payment->xendit_invoice_id)
            <div class="md:col-span-2">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Xendit Invoice ID</h3>
                <p class="text-gray-900 font-mono text-sm">{{ $payment->xendit_invoice_id }}</p>
            </div>
            @endif
        </div>

        @if($payment->metadata)
        <div class="mt-6 pt-6 border-t">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Metadata</h3>
            <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto">{{ json_encode($payment->metadata, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
    </div>

    <!-- Payment Logs -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Payment Logs</h3>
        </div>
        <div class="p-6">
            @forelse($payment->logs as $log)
            <div class="mb-4 pb-4 border-b last:border-b-0">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="font-semibold text-gray-900">{{ $log->event }}</span>
                        <p class="text-sm text-gray-500 mt-1">{{ $log->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                </div>
                <pre class="bg-gray-100 p-3 rounded text-xs mt-2 overflow-x-auto">{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No logs yet</p>
            @endforelse
        </div>
    </div>

    <!-- Webhook Logs -->
    @if($payment->webhookLogs->count() > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Webhook Logs</h3>
        </div>
        <div class="p-6">
            @foreach($payment->webhookLogs as $webhook)
            <div class="mb-4 pb-4 border-b last:border-b-0">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="font-semibold text-gray-900">{{ $webhook->event_type }}</span>
                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $webhook->status == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($webhook->status) }}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">{{ $webhook->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                </div>
                @if($webhook->response)
                <div class="mt-2">
                    <p class="text-sm font-semibold text-gray-700">Response:</p>
                    <pre class="bg-gray-100 p-3 rounded text-xs mt-1 overflow-x-auto">{{ json_encode($webhook->response, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
