@extends('layouts.customer')
@section('title', __('Order details'))
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ __('Order details') }}</h1><div class="panel"><p class="break-all font-semibold">{{ $order->order_number }}</p><p>{{ __('Status') }}: {{ __('status.'.$order->status) }} · {{ __('Payment') }}: {{ __('status.'.$order->payment_status) }}</p><ul class="my-4">@foreach($order->items as $item)<li>{{ $item->product->name }} × {{ $item->quantity }}: {{ \App\Support\Money::format($item->subtotal) }}</li>@endforeach</ul><p class="font-semibold">{{ __('Total') }}: {{ \App\Support\Money::format($order->total) }}</p><p>{{ __('Payment is pending until verified by the store or gateway.') }}</p><a class="inline-block mt-4" href="{{ route('customer.history') }}">{{ __('My orders') }}</a></div>
<script>localStorage.removeItem('warbun_cart');localStorage.removeItem('warbun_checkout_key');</script>
@endsection
