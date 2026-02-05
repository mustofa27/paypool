<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PayPool Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    @auth
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-2xl font-bold text-indigo-600">PayPool</h1>
                    </div>
                    <!-- Desktop Menu -->
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="{{ route('admin.dashboard') }}" class="@if(request()->routeIs('admin.dashboard')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-chart-line mr-2"></i> Dashboard
                        </a>
                        <a href="{{ route('admin.apps.index') }}" class="@if(request()->routeIs('admin.apps.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-layer-group mr-2"></i> Apps
                        </a>
                        <a href="{{ route('admin.payments.index') }}" class="@if(request()->routeIs('admin.payments.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-money-bill-wave mr-2"></i> Payments
                        </a>
                        <a href="{{ route('admin.test-payment') }}" class="@if(request()->routeIs('admin.test-payment')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-flask mr-2"></i> Test Payment
                        </a>
                    </div>
                </div>
                <!-- Desktop User Menu -->
                <div class="hidden md:flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700 text-sm">{{ auth()->user()->name }}</span>
                            <a href="{{ route('admin.profile.change-password') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                                <i class="fas fa-key"></i> <span class="hidden lg:inline">Change Password</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm">
                                    <i class="fas fa-sign-out-alt"></i> <span class="hidden lg:inline">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button type="button" onclick="toggleMobileMenu()" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <i class="fas fa-bars text-xl" id="menuOpenIcon"></i>
                        <i class="fas fa-times text-xl hidden" id="menuCloseIcon"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="hidden md:hidden" id="mobileMenu">
            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="@if(request()->routeIs('admin.dashboard')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-chart-line mr-2"></i> Dashboard
                </a>
                <a href="{{ route('admin.apps.index') }}" class="@if(request()->routeIs('admin.apps.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-layer-group mr-2"></i> Apps
                </a>
                <a href="{{ route('admin.payments.index') }}" class="@if(request()->routeIs('admin.payments.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-money-bill-wave mr-2"></i> Payments
                </a>
                <a href="{{ route('admin.test-payment') }}" class="@if(request()->routeIs('admin.test-payment')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-flask mr-2"></i> Test Payment
                </a>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-4 mb-3">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <div class="space-y-1">
                    <a href="{{ route('admin.profile.change-password') }}" class="block px-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-key mr-2"></i> Change Password
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left block px-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        const openIcon = document.getElementById('menuOpenIcon');
        const closeIcon = document.getElementById('menuCloseIcon');
        
        menu.classList.toggle('hidden');
        openIcon.classList.toggle('hidden');
        closeIcon.classList.toggle('hidden');
    }
    </script>
    @endauth

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('new_token') && !request()->routeIs('admin.apps.show'))
    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="bg-gradient-to-r from-yellow-100 to-orange-100 border-2 border-yellow-400 px-6 py-4 rounded-lg shadow-lg" role="alert">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mr-4 mt-1"></i>
                <div class="flex-1">
                    <strong class="font-bold text-lg text-yellow-900">Access Token Generated!</strong>
                    <p class="text-sm text-yellow-800 mt-1">Copy this token now - it will only be shown once for security reasons.</p>
                    <div class="flex items-center gap-2 mt-3">
                        <input type="text" 
                               id="flashToken" 
                               value="{{ session('new_token') }}" 
                               readonly
                               class="flex-1 p-2 bg-white border border-yellow-400 rounded font-mono text-sm">
                        <button onclick="copyFlashToken()" 
                                class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    function copyFlashToken() {
        const tokenInput = document.getElementById('flashToken');
        tokenInput.select();
        navigator.clipboard.writeText(tokenInput.value).then(function() {
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            btn.classList.remove('bg-yellow-600', 'hover:bg-yellow-700');
            btn.classList.add('bg-green-600');
            setTimeout(function() {
                btn.innerHTML = originalHTML;
                btn.classList.remove('bg-green-600');
                btn.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
            }, 2000);
        });
    }
    </script>
    @endif

    @if($errors->any())
    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; 2024 PayPool - Xendit Payment Bridge. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html>
