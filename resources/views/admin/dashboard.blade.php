@extends('layouts.app')

@section('title', 'Dashboard - PayPool Admin')

@section('content')
<div class="px-4 sm:px-0">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
        <p class="text-sm text-gray-600 mt-1">Overview of your payment gateway statistics</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Apps -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-layer-group text-3xl text-indigo-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Apps</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $stats['total_apps'] }}</dd>
                            <dd class="text-sm text-green-600">{{ $stats['active_apps'] }} active</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Payments -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-receipt text-3xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Payments</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_payments']) }}</dd>
                            <dd class="text-sm text-gray-600">{{ $stats['payments_today'] }} today</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-money-bill-wave text-3xl text-green-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_revenue'], 0) }}</dd>
                            <dd class="text-sm text-gray-600">IDR</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Revenue -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line text-3xl text-yellow-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Today's Revenue</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ number_format($stats['revenue_today'], 0) }}</dd>
                            <dd class="text-sm text-gray-600">IDR</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Status Distribution -->
    <div class="mt-8">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Status Distribution</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach(['pending' => 'yellow', 'paid' => 'green', 'expired' => 'red', 'failed' => 'gray'] as $status => $color)
                <div class="text-center">
                    <div class="text-3xl font-bold text-{{ $color }}-600">
                        {{ $stats['payments_by_status'][$status] ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-600 capitalize">{{ $status }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="mt-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Payments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">External ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">App</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($stats['recent_payments'] as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.payments.show', $payment) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $payment->external_id }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->app->name }}</td>
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
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No payments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
