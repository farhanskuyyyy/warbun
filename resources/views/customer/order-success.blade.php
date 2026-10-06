@extends('layouts.customer')
@section('title', __('Order details'))
@section('content')
@include('components.shopping-steps', ['current' => 3])
<div class="catalog-heading"><p class="eyebrow">{{ __('Your Warbun order') }}</p><h1>{{ $order->status === 'pending' ? __('Order received') : __('Order details') }}</h1><p>{{ __('Follow the status of this order from your account.') }}</p></div>
<div class="order-detail-layout">
    <section class="panel"><p class="eyebrow">{{ __('Reference') }}</p><h2 class="order-reference">{{ $order->order_number }}</h2><div class="order-status-row"><span>{{ __('Status') }}: <x-transaction-status :status="$order->status" /></span><span>{{ __('Payment') }}: <x-transaction-status :status="$order->payment_status" /></span></div>
        <ul class="order-item-list">@foreach($order->items as $item)<li><div><strong>{{ $item->product->name }}</strong><span>{{ __('Quantity') }}: {{ $item->quantity }}</span></div><span>{{ \App\Support\Money::format($item->subtotal) }}</span></li>@endforeach</ul>
        <dl class="checkout-totals"><div><dt>{{ __('Subtotal') }}</dt><dd>{{ \App\Support\Money::format($order->subtotal) }}</dd></div><div><dt>{{ __('Shipping fee') }}</dt><dd>{{ \App\Support\Money::format($order->shipping_cost) }}</dd></div><div class="checkout-total"><dt>{{ __('Total') }}</dt><dd>{{ \App\Support\Money::format($order->total) }}</dd></div></dl>
    </section>
    <section class="checkout-summary"><h2>{{ __('What happens next?') }}</h2><p class="checkout-help">{{ __('Fulfillment') }}: <strong>{{ $order->fulfillment_type === 'delivery' ? __('Delivery') : __('Pickup') }}</strong></p>@if($order->shipping_address)<p class="checkout-help">{{ $order->shipping_address }}</p>@endif<x-delivery-map-link :latitude="$order->shipping_latitude" :longitude="$order->shipping_longitude" />
        @if($order->payment_status === 'pending' && $order->status !== 'cancelled')<p class="whitespace-pre-line">{{ $order->payments->first()?->method === 'cash' ? __('Pay when you collect or receive your order.') : ($paymentInstructions ?: __('Contact the store for transfer, e-wallet or QR payment details. Payment is confirmed by the store.')) }}</p><p class="checkout-help">{{ __('Payment is pending until verified by the store or gateway.') }}</p>@endif
        @if($storeContact)<p class="checkout-help">{{ __('Store contact') }}: {{ $storeContact }}</p>@endif
        @if($order->notes)<p class="checkout-help">{{ __('Notes') }}: {{ $order->notes }}</p>@endif
        <a class="button-primary" href="{{ route('customer.history') }}">{{ __('My orders') }}</a><a class="continue-shopping" href="{{ route('customer.shop') }}">{{ __('Continue shopping') }}</a>
    </section>
</div>
@endsection
@push('scripts')
<script>
if (localStorage.getItem('warbun_checkout_key') === @json($order->request_key)) {
    localStorage.removeItem('warbun_cart'); localStorage.removeItem('warbun_checkout_key');
    sessionStorage.removeItem('warbun_checkout_draft');
    window.dispatchEvent(new Event('warbun:cartchange'));
}
</script>
@endpush
