@extends('layouts.app')
@section('title', __('Online Orders'))
@section('header', __('Online Orders'))

@section('content')
<div class="space-y-4">
    <a class="button-primary" href="{{ route('orders.monitor') }}">{{ __('Order monitoring') }}</a>
    <!-- Filters -->
    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <form method="GET" class="flex gap-2 flex-wrap">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('All Status') }}</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                <option value="preparing" {{ request('status') == 'preparing' ? 'selected' : '' }}>{{ __('Preparing') }}</option>
                <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>{{ __('Ready') }}</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
            </select>
            <select name="payment_status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('All Payment') }}</option>
                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">{{ __('Filter') }}</button>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Order #') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Customer') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Total') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Status') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Payment') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Date') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $order->order_number }}</td>
                    <td class="px-4 py-3">{{ $order->customer->name }}</td>
                    <td class="px-4 py-3 text-right font-medium">{{ \App\Support\Money::format($order->total) }}</td>
                    <td class="px-4 py-3">
                        @php $statusColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'confirmed' => 'bg-blue-100 text-blue-700', 'preparing' => 'bg-purple-100 text-purple-700', 'ready' => 'bg-green-100 text-green-700', 'completed' => 'bg-gray-100 text-gray-700', 'cancelled' => 'bg-red-100 text-red-700']; @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$order->status] ?? '' }}">{{ __('status.'.$order->status) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @php $payColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'paid' => 'bg-green-100 text-green-700']; @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $payColors[$order->payment_status] ?? '' }}">{{ __('status.'.$order->payment_status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $order->created_at->translatedFormat('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('orders.show', $order) }}" class="text-primary hover:text-primary-dark">{{ __('View') }}</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ __('No orders found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $orders->withQueryString()->links() }}</div>
</div>
@endsection
