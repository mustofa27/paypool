@extends('layouts.app')

@section('title', 'Test Payment Redirect Result')

@section('content')
<div class="max-w-xl mx-auto mt-10">
    <div class="bg-white shadow rounded-lg p-8 text-center">
        @if($type === 'success')
            <div class="text-green-600 text-4xl mb-4">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="text-2xl font-bold mb-2">Payment Success</h2>
            <p class="mb-4">The test payment was completed successfully.</p>
        @elseif($type === 'failure')
            <div class="text-red-600 text-4xl mb-4">
                <i class="fas fa-times-circle"></i>
            </div>
            <h2 class="text-2xl font-bold mb-2">Payment Failed</h2>
            <p class="mb-4">The test payment failed or was cancelled.</p>
        @elseif($type === 'unfinish')
            <div class="text-yellow-500 text-4xl mb-4">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h2 class="text-2xl font-bold mb-2">Payment Unfinished</h2>
            <p class="mb-4">The test payment was not completed.</p>
        @endif
        <div class="mt-6 text-left text-sm text-gray-700">
            <div><strong>Order ID:</strong> {{ $orderId ?? '-' }}</div>
            <div><strong>Status Code:</strong> {{ $statusCode ?? '-' }}</div>
            <div><strong>Transaction Status:</strong> {{ $transactionStatus ?? '-' }}</div>
        </div>
        <a href="{{ route('test-payment') }}" class="mt-8 inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
            <i class="fas fa-arrow-left mr-2"></i>Back to Test Payment
        </a>
    </div>
</div>
@endsection
