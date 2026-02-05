@extends('layouts.app')

@section('title', 'Change Password - PayPool Admin')

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Change Password</h1>
            <p class="mt-2 text-gray-600">Update your account password</p>
        </div>

        <!-- Change Password Form -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <form method="POST" action="{{ route('admin.profile.update-password') }}" class="p-6">
                @csrf
                @method('PUT')

                <!-- Current Password -->
                <div class="mb-6">
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Current Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="current_password" 
                            id="current_password" 
                            required
                            class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('current_password') border-red-500 @enderror">
                        <button type="button" 
                                onclick="togglePasswordField('current_password', 'toggleCurrentIcon')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                            <i class="fas fa-eye" id="toggleCurrentIcon"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            required
                            class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('password') border-red-500 @enderror">
                        <button type="button" 
                                onclick="togglePasswordField('password', 'toggleNewIcon')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                            <i class="fas fa-eye" id="toggleNewIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Minimum 8 characters</p>
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="password_confirmation" 
                            required
                            class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <button type="button" 
                                onclick="togglePasswordField('password_confirmation', 'toggleConfirmIcon')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                            <i class="fas fa-eye" id="toggleConfirmIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t">
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                    </a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition">
                        <i class="fas fa-key mr-2"></i> Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Security Tips -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-blue-900 mb-2">
                <i class="fas fa-info-circle mr-2"></i> Password Security Tips
            </h3>
            <ul class="text-sm text-blue-800 space-y-1 ml-6 list-disc">
                <li>Use at least 8 characters</li>
                <li>Include uppercase and lowercase letters</li>
                <li>Include numbers and special characters</li>
                <li>Don't use common words or personal information</li>
                <li>Don't reuse passwords from other accounts</li>
            </ul>
        </div>
    </div>
</div>

<script>
function togglePasswordField(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection
