<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Warbun · @yield('title', __('Shop'))</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="store-shell">
<a class="skip-link" href="#main">{{ __('Skip to content') }}</a>
<header class="store-header"><nav class="store-nav" aria-label="{{ __('Main navigation') }}">
    <a href="{{ route('landing') }}" class="brand">warbun<span>{{ __('Your everyday essentials') }}</span></a>
    <a href="{{ route('customer.shop') }}" class="store-nav-link {{ request()->routeIs('customer.shop','customer.product') ? 'is-active' : '' }}"><x-icon name="store" /><span>{{ __('Shop') }}</span></a>
    <div class="store-tools"><a class="cart-link" href="{{ route('customer.cart') }}" title="{{ __('Cart') }}"><x-icon name="cart-shopping" /><span class="sr-only">{{ __('Cart') }}</span><span data-cart-count class="cart-count">0</span></a>@include('components.account-menu')</div>
</nav></header>
<script id="shopping-messages" type="application/json">{!! json_encode(['items' => __(':count items'), 'inCart' => __(':count in cart'), 'added' => __('Added to cart'), 'limit' => __('You have reached the available stock.')], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<main id="main" class="store-content">@include('components.feedback') @yield('content')</main>
<footer class="store-footer">
    <div class="footer-top">
        <div><a href="{{ route('landing') }}" class="brand">warbun</a><p>{{ __('Everyday shopping, a little easier.') }}</p></div>
        <nav aria-label="{{ __('Footer navigation') }}">
            <a href="{{ route('customer.shop') }}">{{ __('Browse products') }}</a>
            <a href="{{ route('customer.cart') }}">{{ __('Cart') }}</a>
            @auth
                @if(auth()->user()->customer)<a href="{{ route('customer.history') }}">{{ __('My orders') }}</a>@else<a href="{{ route('profile.edit') }}">{{ __('Profile') }}</a>@endif
            @else
                <a href="{{ route('register') }}">{{ __('Create an account') }}</a>
            @endauth
        </nav>
    </div>
    <div class="footer-bottom"><span>© {{ date('Y') }} Warbun</span><span>{{ __('Pickup or delivery. Your choice.') }}</span></div>
</footer>@stack('scripts')</body></html>
