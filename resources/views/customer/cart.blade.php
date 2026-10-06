@extends('layouts.customer')
@section('title', __('Cart'))
@section('content')
@include('components.shopping-steps', ['current' => 2])
<div class="catalog-heading"><h1>{{ __('Review your shopping') }}</h1><p>{{ __('Check your items, choose how to receive them, then place your order.') }}</p></div>
<div class="checkout-layout">
    <section aria-label="{{ __('Cart items') }}"><div id="cartItems" class="cart-items"></div><a class="continue-shopping" href="{{ route('customer.shop') }}">← {{ __('Continue shopping') }}</a></section>
    <form id="checkoutForm" action="{{ route('customer.checkout') }}" method="POST" class="checkout-summary">
        @csrf
        <h2 id="checkout">{{ __('Order summary') }}</h2>
        <div class="field"><label for="delivery-type">{{ __('How would you like to receive your order?') }}</label><select id="delivery-type" name="delivery_type"><option value="pickup" @selected(old('delivery_type') === 'pickup')>{{ __('Pickup') }}</option><option value="delivery" @selected(old('delivery_type') === 'delivery')>{{ __('Delivery') }}</option></select></div>
        <p id="fulfillment-help" class="checkout-help">{{ __('Collect your order at the store. No shipping fee.') }}</p>
        <div id="shipping-address-field" class="field" hidden><x-address-selector address-id="shipping-address" address-name="address" point-id="cartDelivery" />@if(auth()->user()?->customer)<a class="continue-shopping" href="{{ route('customer.addresses') }}">{{ __('Manage addresses') }}</a>@endif</div>
        <div class="field"><label for="payment-method">{{ __('Payment Method') }}</label><select id="payment-method" name="payment_method">@foreach(['cash','transfer','ewallet','qr'] as $method)<option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ __('status.'.$method) }}</option>@endforeach @if(config('payments.gateway') === 'signed-webhook')<option value="online">{{ __('status.online') }}</option>@endif</select><p id="payment-help" class="checkout-help"></p></div>
        @if($storeContact)<p class="checkout-help">{{ __('Store contact') }}: {{ $storeContact }}</p>@endif
        <details class="checkout-notes"><summary>{{ __('Add a note (optional)') }}</summary><label class="field mt-2" for="order-notes">{{ __('Notes') }}<textarea id="order-notes" name="notes" maxlength="2000" rows="2">{{ old('notes') }}</textarea></label></details>
        <dl class="checkout-totals"><div><dt>{{ __('Subtotal') }}</dt><dd id="cartTotal">…</dd></div><div><dt>{{ __('Shipping fee') }}</dt><dd id="shippingTotal">…</dd></div><div class="checkout-total"><dt>{{ __('Total') }}</dt><dd id="orderTotal">…</dd></div></dl>
        <p id="quote-status" role="status" class="checkout-help"></p><p id="quote-errors" role="alert" class="field-error"></p>
        <button id="quote-retry" type="button" class="continue-shopping" hidden>{{ __('Try again') }}</button>
        <p class="checkout-help">{{ __('Final total and stock are verified at checkout.') }}</p>
        @if(auth()->user()?->customer)<button id="checkoutBtn" class="button-primary" type="submit" disabled>{{ __('Place order') }}</button>@elseif(auth()->check())<p class="checkout-help">{{ __('Shopping checkout requires a customer account.') }}</p>@else<a data-checkout-action class="button-primary" href="{{ route('customer.checkout.continue') }}">{{ __('Sign in and continue') }}</a><p class="checkout-help">{{ __('Your cart is saved while you sign in or create an account.') }}</p>@endif
    </form>
</div>
<script id="checkout-config" type="application/json">{!! json_encode([
    'quoteUrl' => route('customer.cart.quote'),
    'hasOldInput' => session()->hasOldInput(),
    'customerId' => auth()->user()?->customer?->id,
    'csrf' => csrf_token(),
    'messages' => [
        'empty' => __('Your cart is empty'), 'emptyHelp' => __('Choose your everyday essentials from the catalog.'), 'shop' => __('Browse products'), 'shopUrl' => route('customer.shop'),
        'quantity' => __('Quantity'), 'remove' => __('Remove'), 'decrease' => __('Decrease quantity'), 'increase' => __('Increase quantity'), 'unavailable' => __('Unavailable product'),
        'checking' => __('Checking prices and stock…'), 'ready' => __('Prices and stock checked. Ready to order.'), 'error' => __('We could not check your cart. Please try again.'),
        'pickup' => __('Collect your order at the store. No shipping fee.'), 'delivery' => __('Your shipping fee is included in the total below.'),
        'cash' => __('Pay when you collect or receive your order.'), 'manual' => $paymentInstructions ?: __('Contact the store for transfer, e-wallet or QR payment details. Payment is confirmed by the store.'), 'online' => __('Online payment requires confirmation from the payment provider.'),
        'placing' => __('Placing your order…'), 'available' => __('Available: :count'),
    ],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@include('components.delivery-map-dialog')
@include('components.address-book')
@endsection
