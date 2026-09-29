@extends('layouts.app')
@section('title', 'Order Details')
@section('header', 'Order ' . $order->order_number)

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</p>
        </div>
        <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">Back</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Order Info -->
        <div class="lg:col-span-2 bg-white rounded-xl p-6 border border-gray-100">
            <h3 class="font-semibold mb-4">Order Items</h3>
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200">
                    <tr>
                        <th class="text-left py-2">Product</th>
                        <th class="text-right py-2">Qty</th>
                        <th class="text-right py-2">Price</th>
                        <th class="text-right py-2">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr class="border-b border-gray-100">
                        <td class="py-2">{{ $item->product->name }}</td>
                        <td class="py-2 text-right">{{ $item->quantity }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="py-2 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-gray-200">
                    <tr>
                        <td colspan="3" class="py-2 text-right font-bold">Total</td>
                        <td class="py-2 text-right font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Status -->
        <div class="bg-white rounded-xl p-6 border border-gray-100">
            <h3 class="font-semibold mb-4">Status</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-500">Order Status</p>
                    <span class="px-3 py-1 text-sm rounded-full {{ $statusColors[$order->status] ?? '' }}">{{ ucfirst($order->status) }}</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Payment Status</p>
                    <span class="px-3 py-1 text-sm rounded-full {{ $payColors[$order->payment_status] ?? '' }}">{{ ucfirst($order->payment_status) }}</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Customer</p>
                    <p class="font-medium">{{ $order->customer->name }}</p>
                </div>
                @if($order->shipping_address)
                <div>
                    <p class="text-xs text-gray-500">Address</p>
                    <p class="text-sm">{{ $order->shipping_address }}</p>
                </div>
                @endif
            </div>

            <!-- Status Update -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500 mb-2">Update Status</p>
                <form action="{{ route('orders.update-status', $order) }}" method="POST" class="space-y-2">
                    @csrf
                    @method('PUT')
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="confirmed">Confirmed</option>
                        <option value="preparing">Preparing</option>
                        <option value="ready">Ready</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg text-sm font-medium">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
