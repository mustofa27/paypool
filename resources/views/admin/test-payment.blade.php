@extends('layouts.app')

@section('title', 'Test Payment - PayPool Admin')

@section('content')
<div class="px-4 sm:px-0">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Create Test Payment</h2>
        <p class="text-sm text-gray-600 mt-1">
            <i class="fas fa-flask text-blue-500"></i> Create dummy payments for testing without calling Xendit API
        </p>
    </div>

    @if($apps->isEmpty())
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-6" role="alert">
        <strong class="font-bold">No Apps Found!</strong>
        <span class="block sm:inline">You need to create an app first before creating test payments.</span>
        <a href="{{ route('admin.apps.create') }}" class="underline font-semibold">Create an app now</a>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Test Payment Form -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-plus-circle text-indigo-600"></i> Create Test Payment
            </h3>

            <form method="POST" action="{{ route('admin.test-payment.store') }}">
                @csrf

                <div class="mb-4">
                    <label for="app_id" class="block text-gray-700 text-sm font-bold mb-2">
                        Select App <span class="text-red-500">*</span>
                    </label>
                    <select id="app_id" 
                            name="app_id" 
                            required
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline focus:border-indigo-500 @error('app_id') border-red-500 @enderror">
                        <option value="">Choose an app...</option>
                        @foreach($apps as $app)
                        <option value="{{ $app->id }}" {{ old('app_id') == $app->id ? 'selected' : '' }}>
                            {{ $app->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('app_id')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="amount" class="block text-gray-700 text-sm font-bold mb-2">
                        Amount (IDR) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="amount" 
                           name="amount" 
                           value="{{ old('amount', 100000) }}"
                           min="10000"
                           step="1000"
                           required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500 @error('amount') border-red-500 @enderror">
                    @error('amount')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="customer_name" class="block text-gray-700 text-sm font-bold mb-2">
                        Customer Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="customer_name" 
                           name="customer_name" 
                           value="{{ old('customer_name', 'Test Customer') }}"
                           required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500 @error('customer_name') border-red-500 @enderror">
                    @error('customer_name')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="customer_email" class="block text-gray-700 text-sm font-bold mb-2">
                        Customer Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="customer_email" 
                           name="customer_email" 
                           value="{{ old('customer_email', 'test@example.com') }}"
                           required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500 @error('customer_email') border-red-500 @enderror">
                    @error('customer_email')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="customer_phone" class="block text-gray-700 text-sm font-bold mb-2">
                        Customer Phone
                    </label>
                    <input type="text" 
                           id="customer_phone" 
                           name="customer_phone" 
                           value="{{ old('customer_phone', '+628123456789') }}"
                           placeholder="+628123456789"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500">
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500">{{ old('description', 'Test payment for development') }}</textarea>
                </div>

                <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-plus mr-2"></i>Create Test Payment
                </button>
            </form>
        </div>

        <!-- Instructions -->
        <div class="space-y-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-3">
                    <i class="fas fa-info-circle"></i> How Test Payments Work
                </h3>
                <ul class="text-sm text-blue-800 space-y-2">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                        <span>Test payments use <strong>real Xendit API</strong> (sandbox/development)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                        <span>You get a real payment URL to complete the payment</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                        <span>After payment, Xendit sends a real webhook automatically</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                        <span>Payment status updates automatically when you pay</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-blue-600 mr-2 mt-1"></i>
                        <span>Or manually mark as paid/expired to test webhook handling</span>
                    </li>
                </ul>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-900 mb-3">
                    <i class="fas fa-lightbulb"></i> Testing Workflow
                </h3>
                <ol class="text-sm text-green-800 space-y-2">
                    <li class="flex items-start">
                        <span class="font-bold text-green-600 mr-3 w-6">1.</span>
                        <span>Fill the form and create a test payment</span>
                    </li>
                    <li class="flex items-start">
                        <span class="font-bold text-green-600 mr-3 w-6">2.</span>
                        <span>You'll see a real Xendit payment URL</span>
                    </li>
                    <li class="flex items-start">
                        <span class="font-bold text-green-600 mr-3 w-6">3.</span>
                        <span><strong>Option A:</strong> Complete the payment on Xendit → Webhook auto-triggers</span>
                    </li>
                    <li class="flex items-start">
                        <span class="font-bold text-green-600 mr-3 w-6">4.</span>
                        <span><strong>Option B:</strong> Click "Mark Paid" to simulate payment → Webhook auto-triggers</span>
                    </li>
                    <li class="flex items-start">
                        <span class="font-bold text-green-600 mr-3 w-6">5.</span>
                        <span>Check your app's webhook endpoint - it should receive the update!</span>
                    </li>
                </ol>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-yellow-900 mb-3">
                    <i class="fas fa-exclamation-triangle"></i> Important Notes
                </h3>Uses Xendit <strong>Development/Sandbox API</strong></span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-exclamation-circle text-yellow-600 mr-2 mt-1"></i>
                        <span>Invoice URLs are from Xendit staging environment</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-exclamation-circle text-yellow-600 mr-2 mt-1"></i>
                        <span>No real money is involved</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-exclamation-circle text-yellow-600 mr-2 mt-1"></i>
                        <span>Perfect for testing webhook integration end-to-end</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-exclamation-circle text-yellow-600 mr-2 mt-1"></i>
                        <span>Make sure your webhook URL is publicly accessible (or use ngrok)
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-exclamation-circle text-yellow-600 mr-2 mt-1"></i>
                        <span>For production testing, use Xendit's sandbox mode</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
