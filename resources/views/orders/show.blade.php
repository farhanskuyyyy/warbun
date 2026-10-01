@extends('layouts.app')
@section('title', __('Order Details'))
@section('header', __('Order Details'))
@section('content')
@php
    $next = ['pending'=>'confirmed','confirmed'=>'preparing','preparing'=>'ready','ready'=>'completed'][$order->status] ?? null;
    $permission = $next === 'confirmed' ? 'orders.confirm' : ($next === 'completed' ? 'orders.complete' : 'orders.update');
    $actions = ['confirmed'=>'Confirm order','preparing'=>'Start preparing','ready'=>'Mark ready','completed'=>'Mark completed'];
    $received = $order->payments->where('status','paid')->sum(fn($p) => \App\Support\Money::cents($p->amount));
    $remaining = max(0,\App\Support\Money::cents($order->total)-$received);
@endphp
<div class="monitor-heading"><div><h2>{{ $order->customer?->name }}</h2><p class="break-all">{{ $order->order_number }}</p><p>{{ $order->created_at->translatedFormat('d M Y H:i') }}</p></div><a class="button-secondary" href="{{ route('orders.monitor') }}">{{ __('Order monitoring') }}</a></div>
<div class="order-detail-layout">
    <section class="panel"><h2>{{ __('Order Items') }}</h2>@foreach($order->items as $item)<div class="pos-cart-item"><strong>{{ $item->product?->name }}</strong><p>{{ $item->quantity }} × {{ \App\Support\Money::format($item->unit_price) }} · {{ \App\Support\Money::format($item->subtotal) }}</p></div>@endforeach<dl class="mt-5"><div><dt>{{ __('Shipping fee') }}</dt><dd>{{ \App\Support\Money::format($order->shipping_cost) }}</dd></div><div><dt>{{ __('Total') }}</dt><dd class="text-2xl font-semibold">{{ \App\Support\Money::format($order->total) }}</dd></div></dl></section>
    <section class="panel"><h2>{{ __('Status') }}</h2><dl><div><dt>{{ __('Order Status') }}</dt><dd><x-transaction-status :status="$order->status" /></dd></div><div><dt>{{ __('Payment Status') }}</dt><dd><x-transaction-status :status="$order->payment_status" /></dd></div><div><dt>{{ $order->fulfillment_type === 'delivery' ? __('Delivery address') : __('Pickup') }}</dt><dd>{{ $order->shipping_address ?: __('Collect at the store') }}</dd></div><div><dt>{{ __('Phone') }}</dt><dd>{{ $order->customer?->phone }}</dd></div>@if($order->notes)<div><dt>{{ __('Order notes') }}</dt><dd>{{ $order->notes }}</dd></div>@endif</dl>
    @if($next === 'completed' && $order->payment_status !== 'paid')<p class="monitor-notice mb-4">{{ __('Record payment before completing this order.') }}</p>@endif
    @if($next && auth()->user()->can('orders.update') && auth()->user()->can($permission) && ($next !== 'completed' || $order->payment_status === 'paid'))<form method="POST" action="{{ route('orders.update-status',$order) }}">@csrf @method('PUT')<input name="status" type="hidden" value="{{ $next }}"><button class="button-primary w-full">{{ __($actions[$next]) }}</button></form>@endif
    @if($next && ! $order->payments->whereIn('status',['paid','refunded'])->count() && !in_array($order->payment_status,['paid','partial']))@can('orders.update')@can('orders.cancel')<form method="POST" action="{{ route('orders.update-status',$order) }}" class="mt-4">@csrf @method('PUT')<input name="status" type="hidden" value="cancelled"><button class="button-secondary w-full">{{ __('Cancel Order') }}</button></form>@endcan @endcan @endif
    </section>
</div>
@can('payments.create')
@if(!in_array($order->status,['completed','cancelled','refunded']) && $remaining > 0)
<form method="POST" action="{{ route('orders.payment',$order) }}" class="panel mt-6 space-y-4">@csrf<input type="hidden" name="request_key" value="{{ old('request_key',(string)\Illuminate\Support\Str::uuid()) }}"><h2 class="text-xl font-semibold">{{ __('Record Payment') }}</h2><label class="field">{{ __('Amount') }}<input name="amount" type="number" min="0.01" max="{{ \App\Support\Money::decimal($remaining) }}" step="0.01" value="{{ old('amount',\App\Support\Money::decimal($remaining)) }}" required></label><label class="field">{{ __('Method') }}<select name="method">@foreach(['cash','transfer','ewallet','qr'] as $method)<option value="{{ $method }}">{{ __('status.'.$method) }}</option>@endforeach</select></label><button class="button-primary">{{ __('Record Payment') }}</button></form>
@endif
@endcan
@endsection
