@extends('layouts.app')

@section('title', $app->name . ' - PayPool Admin')

@section('content')
<div class="px-4 sm:px-0">
    <div class="mb-6">
        <a href="{{ route('admin.apps.index') }}" class="text-indigo-600 hover:text-indigo-900">
            <i class="fas fa-arrow-left mr-2"></i>Back to Apps
        </a>
    </div>

    <!-- App Details -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $app->name }}</h2>
                <p class="text-gray-600 mt-2">{{ $app->description }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.apps.edit', $app) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form method="POST" action="{{ route('admin.apps.destroy', $app) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            onclick="return confirm('Are you sure you want to deactivate this app?')"
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-ban"></i> Deactivate
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Status</h3>
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $app->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $app->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Created By</h3>
                <p class="text-gray-900">{{ $app->creator->name }}</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Webhook URL</h3>
                <p class="text-gray-900 text-sm">{{ $app->webhook_url ?: 'Not set' }}</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Success Redirect URL</h3>
                <p class="text-gray-900 text-sm">{{ $app->success_redirect_url ?: 'Not set' }}</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Failure Redirect URL</h3>
                <p class="text-gray-900 text-sm">{{ $app->failure_redirect_url ?: 'Not set' }}</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Created At</h3>
                <p class="text-gray-900">{{ $app->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t">
            <div class="text-center">
                <div class="text-3xl font-bold text-indigo-600">{{ $app->payments_count }}</div>
                <div class="text-sm text-gray-600">Total Payments</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $app->webhook_logs_count }}</div>
                <div class="text-sm text-gray-600">Webhook Logs</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $app->updated_at->diffForHumans() }}</div>
                <div class="text-sm text-gray-600">Last Updated</div>
            </div>
        </div>
    </div>

    <!-- Access Token -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Access Token</h3>
            <form method="POST" action="{{ route('admin.apps.regenerate-token', $app) }}">
                @csrf
                <button type="submit" 
                        onclick="return confirm('⚠️ Warning: This will invalidate the current token and all apps using it will stop working!\n\nAre you sure you want to continue?')"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-sync-alt"></i> Regenerate Token
                </button>
            </form>
        </div>
        <p class="text-sm text-gray-600 mb-4">
            <i class="fas fa-info-circle text-blue-500"></i> Use this token to authenticate API requests from your application. Add it as a Bearer token in the Authorization header.
        </p>
        
        @if(session('new_token'))
            <!-- Show the actual token when just generated -->
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-400 p-4 rounded-lg mb-4">
                <p class="text-sm font-bold text-yellow-800 mb-2">
                    <i class="fas fa-exclamation-triangle"></i> Save this token now! You won't be able to see it again.
                </p>
                <div class="flex items-center gap-2">
                    <input type="text" 
                           id="tokenValue" 
                           value="{{ session('new_token') }}" 
                           readonly
                           class="flex-1 bg-white border border-gray-300 rounded px-3 py-2 text-sm font-mono">
                    <button onclick="copyToken()" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <p class="text-xs text-gray-600 mt-2">
                    <strong>Example usage:</strong> <code class="bg-white px-2 py-1 rounded">Authorization: Bearer {{ session('new_token') }}</code>
                </p>
            </div>
        @else
            <!-- Show masked token -->
            <div class="bg-gray-100 p-4 rounded">
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-800">••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••</code>
                    <span class="text-xs text-gray-500">
                        <i class="fas fa-lock"></i> Hidden for security
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    <i class="fas fa-info-circle"></i> The token was shown only once during creation/regeneration. If you lost it, regenerate a new one above.
                </p>
            </div>
        @endif

        <!-- API Usage Example -->
        <div class="mt-6 pt-6 border-t">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">
                <i class="fas fa-code"></i> API Usage Example
            </h4>
            <div class="bg-gray-900 text-gray-100 p-4 rounded-lg text-xs font-mono overflow-x-auto">
                <span class="text-green-400">// Create Payment</span><br>
                curl -X POST {{ url('/api/v1/payments/create') }} \<br>
                &nbsp;&nbsp;-H <span class="text-yellow-300">"Content-Type: application/json"</span> \<br>
                &nbsp;&nbsp;-H <span class="text-yellow-300">"Authorization: Bearer YOUR_ACCESS_TOKEN"</span> \<br>
                &nbsp;&nbsp;-d <span class="text-yellow-300">'{<br>
                &nbsp;&nbsp;&nbsp;&nbsp;"external_id": "ORDER-001",<br>
                &nbsp;&nbsp;&nbsp;&nbsp;"amount": 100000,<br>
                &nbsp;&nbsp;&nbsp;&nbsp;"customer_name": "John Doe",<br>
                &nbsp;&nbsp;&nbsp;&nbsp;"customer_email": "john@example.com"<br>
                &nbsp;&nbsp;}'</span>
            </div>
        </div>
    </div>

    <script>
    function copyToken() {
        const tokenInput = document.getElementById('tokenValue');
        tokenInput.select();
        tokenInput.setSelectionRange(0, 99999); // For mobile devices
        
        navigator.clipboard.writeText(tokenInput.value).then(function() {
            // Show success message
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            btn.classList.add('bg-green-600');
            
            setTimeout(function() {
                btn.innerHTML = originalHTML;
                btn.classList.remove('bg-green-600');
                btn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            }, 2000);
        }, function(err) {
            alert('Failed to copy token. Please copy manually.');
        });
    }
    </script>

    <!-- Recent Payments -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Payments</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">External ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentPayments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.payments.show', $payment) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $payment->external_id }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->customer_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($payment->amount, 0) }} {{ $payment->currency }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($payment->status == 'paid') bg-green-100 text-green-800
                                @elseif($payment->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($payment->status == 'expired') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No payments yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
