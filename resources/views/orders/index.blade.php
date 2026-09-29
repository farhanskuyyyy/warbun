@extends('layouts.app')
@section('title', 'Online Orders')
@section('header', 'Online Orders')

@section('content')
<div class="space-y-4">
    <!-- Filters -->
    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <form method="GET" class="flex gap-2 flex-wrap">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="preparing" {{ request('status') == 'preparing' ? 'selected' : '' }}>Preparing</option>
                <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <select name="payment_status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">All Payment</option>
                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Filter</button>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Order #</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Customer</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Total</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Payment</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $order->order_number }}</td>
                    <td class="px-4 py-3">{{ $order->customer->name }}</td>
                    <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        @php $statusColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'confirmed' => 'bg-blue-100 text-blue-700', 'preparing' => 'bg-purple-100 text-purple-700', 'ready' => 'bg-green-100 text-green-700', 'completed' => 'bg-gray-100 text-gray-700', 'cancelled' => 'bg-red-100 text-red-700']; @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$order->status] ?? '' }}">{{ $order->status }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @php $payColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'paid' => 'bg-green-100 text-green-700']; @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $payColors[$order->payment_status] ?? '' }}">{{ $order->payment_status }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('orders.show', $order) }}" class="text-primary hover:text-primary-dark">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No orders found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $orders->withQueryString()->links() }}</div>
</div>
@endsection
