@extends('layouts.app')

@section('title', 'Create App - PayPool Admin')

@section('content')
<div class="px-4 sm:px-0">
    <div class="mb-6">
        <a href="{{ route('admin.apps.index') }}" class="text-indigo-600 hover:text-indigo-900">
            <i class="fas fa-arrow-left mr-2"></i>Back to Apps
        </a>
    </div>

    <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Create New App</h2>
        <p class="text-sm text-gray-600 mb-6">
            <i class="fas fa-info-circle text-blue-500"></i> A unique access token will be automatically generated for this app to use with the Payment API.
        </p>

        <form method="POST" action="{{ route('admin.apps.store') }}">
            @csrf

            <div class="mb-6">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                    App Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       required
                       class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500 @error('name') border-red-500 @enderror">
                @error('name')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                    Description
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4"
                          class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="webhook_url" class="block text-gray-700 text-sm font-bold mb-2">
                    Webhook URL
                </label>
                <input type="url" 
                       id="webhook_url" 
                       name="webhook_url" 
                       value="{{ old('webhook_url') }}"
                       placeholder="https://your-app.com/webhooks/payment"
                       class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500 @error('webhook_url') border-red-500 @enderror">
                @error('webhook_url')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-2">This URL will receive payment status updates from PayPool</p>
            </div>

            <div class="mb-6">
                <label for="success_redirect_url" class="block text-gray-700 text-sm font-bold mb-2">
                    Success Redirect URL
                </label>
                <input type="url" 
                       id="success_redirect_url" 
                       name="success_redirect_url" 
                       value="{{ old('success_redirect_url') }}"
                       placeholder="https://your-app.com/payment/success"
                       class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500 @error('success_redirect_url') border-red-500 @enderror">
                @error('success_redirect_url')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-2">Customer will be redirected here after successful payment</p>
            </div>

            <div class="mb-6">
                <label for="failure_redirect_url" class="block text-gray-700 text-sm font-bold mb-2">
                    Failure Redirect URL
                </label>
                <input type="url" 
                       id="failure_redirect_url" 
                       name="failure_redirect_url" 
                       value="{{ old('failure_redirect_url') }}"
                       placeholder="https://your-app.com/payment/failed"
                       class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500 @error('failure_redirect_url') border-red-500 @enderror">
                @error('failure_redirect_url')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-2">Customer will be redirected here if payment fails or is cancelled</p>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-plus mr-2"></i>Create App
                </button>
                <a href="{{ route('admin.apps.index') }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
