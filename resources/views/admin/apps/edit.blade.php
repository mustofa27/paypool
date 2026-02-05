@extends('layouts.app')

@section('title', 'Edit ' . $app->name . ' - PayPool Admin')

@section('content')
<div class="px-4 sm:px-0">
    <div class="mb-6">
        <a href="{{ route('admin.apps.show', $app) }}" class="text-indigo-600 hover:text-indigo-900">
            <i class="fas fa-arrow-left mr-2"></i>Back to App Details
        </a>
    </div>

    <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit App</h2>

        <form method="POST" action="{{ route('admin.apps.update', $app) }}">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                    App Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $app->name) }}"
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
                          class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $app->description) }}</textarea>
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
                       value="{{ old('webhook_url', $app->webhook_url) }}"
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
                       value="{{ old('success_redirect_url', $app->success_redirect_url) }}"
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
                       value="{{ old('failure_redirect_url', $app->failure_redirect_url) }}"
                       placeholder="https://your-app.com/payment/failed"
                       class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-indigo-500 @error('failure_redirect_url') border-red-500 @enderror">
                @error('failure_redirect_url')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-2">Customer will be redirected here if payment fails or is cancelled</p>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $app->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700 font-semibold">Active</span>
                </label>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-save mr-2"></i>Update App
                </button>
                <a href="{{ route('admin.apps.show', $app) }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
