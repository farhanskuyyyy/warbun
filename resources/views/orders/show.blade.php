@extends('layouts.app')
@section('title', __('Order Details'))
@section('header', __('Order').' ' . $order->order_number)

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ $order->created_at->translatedFormat('d M Y H:i') }}</p>
        </div>
        <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">{{ __('Back') }}</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Order Info -->
        <div class="lg:col-span-2 bg-white rounded-xl p-6 border border-gray-100">
            <h3 class="font-semibold mb-4">{{ __('Order Items') }}</h3>
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200">
                    <tr>
                        <th class="text-left py-2">{{ __('Product') }}</th>
                        <th class="text-right py-2">{{ __('Qty') }}</th>
                        <th class="text-right py-2">{{ __('Price') }}</th>
                        <th class="text-right py-2">{{ __('Subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr class="border-b border-gray-100">
                        <td class="py-2">{{ $item->product->name }}</td>
                        <td class="py-2 text-right">{{ $item->quantity }}</td>
                        <td class="py-2 text-right">{{ \App\Support\Money::format($item->unit_price) }}</td>
                        <td class="py-2 text-right font-medium">{{ \App\Support\Money::format($item->subtotal) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-gray-200">
                    <tr>
                        <td colspan="3" class="py-2 text-right font-bold">{{ __('Total') }}</td>
                        <td class="py-2 text-right font-bold">{{ \App\Support\Money::format($order->total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Status -->
        <div class="bg-white rounded-xl p-6 border border-gray-100">
            <h3 class="font-semibold mb-4">{{ __('Status') }}</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-500">{{ __('Order Status') }}</p>
                    <span class="px-3 py-1 text-sm rounded-full {{ $statusColors[$order->status] ?? '' }}">{{ __('status.'.$order->status) }}</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Payment Status') }}</p>
                    <span class="px-3 py-1 text-sm rounded-full {{ $payColors[$order->payment_status] ?? '' }}">{{ __('status.'.$order->payment_status) }}</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Customer') }}</p>
                    <p class="font-medium">{{ $order->customer->name }}</p>
                </div>
                @if($order->shipping_address)
                <div>
                    <p class="text-xs text-gray-500">{{ __('Address') }}</p>
                    <p class="text-sm">{{ $order->shipping_address }}</p>
                </div>
                @endif
            </div>

            <!-- Status Update -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500 mb-2">{{ __('Update Status') }}</p>
                <form action="{{ route('orders.update-status', $order) }}" method="POST" class="space-y-2">
                    @csrf
                    @method('PUT')
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="confirmed">{{ __('Confirmed') }}</option>
                        <option value="preparing">{{ __('Preparing') }}</option>
                        <option value="ready">{{ __('Ready') }}</option>
                        <option value="completed">{{ __('Completed') }}</option>
                        <option value="cancelled">{{ __('Cancelled') }}</option>
                    </select>
                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg text-sm font-medium">{{ __('Update') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@can('payments.create')
@if(!in_array($order->status,['completed','cancelled','refunded']) && $order->payment_status!=='paid')
<form method="POST" action="{{ route('orders.payment',$order) }}" class="panel my-4 space-y-3">@csrf<input type="hidden" name="request_key" value="{{ old('request_key',(string)\Illuminate\Support\Str::uuid()) }}"><h2 class="font-semibold">{{ __('Record Payment') }}</h2><label class="field">{{ __('Amount') }}<input name="amount" type="number" min="0.01" step="0.01" required></label><label class="field">{{ __('Method') }}<select name="method">@foreach(['cash','transfer','ewallet','qr'] as $method)<option value="{{ $method }}">{{ __('status.'.$method) }}</option>@endforeach</select></label><button class="button-primary">{{ __('Record Payment') }}</button></form>
@endif
@endcan
@endsection
