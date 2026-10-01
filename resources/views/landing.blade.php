@extends('layouts.customer')
@section('title', __('Shop'))
@section('content')
<section class="store-hero"><div class="hero-copy"><p class="eyebrow">{{ __('Your everyday essentials') }}</p><h1>{{ __('Everyday shopping, a little easier.') }}</h1><p class="hero-description">{{ __('Browse the catalog, order for pickup or delivery, and track your purchases.') }}</p><a href="{{ route('customer.shop') }}" class="button-primary">{{ __('Browse products') }} <span aria-hidden="true">↗</span></a><div class="hero-note"><span class="status-dot" aria-hidden="true"></span>{{ __('Pickup or delivery. Your choice.') }}</div></div><div class="shelf-preview"><div class="shelf-heading"><span>{{ __('On our shelves') }}</span><span class="shelf-number">01 / WARBUN</span></div>@forelse($featured as $product)<a class="shelf-product" href="{{ route('customer.product', $product) }}"><span class="shelf-index" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span><div><span class="shelf-category">{{ $product->category?->name }}</span><h2>{{ $product->name }}</h2></div><strong>{{ \App\Support\Money::format($product->selling_price) }}</strong><span aria-hidden="true">↗</span></a>@empty<p class="p-6">{{ __('No products available yet') }}</p>@endforelse<div class="shelf-bottom">{{ __('Explore the complete catalog') }} <a href="{{ route('customer.shop') }}" aria-label="{{ __('Browse products') }}">→</a></div></div></section>
<section class="category-section"><div class="section-heading"><div><p class="eyebrow">{{ __('Find what you need') }}</p><h2>{{ __('Shop by category') }}</h2></div><a href="{{ route('customer.shop') }}">{{ __('View all products') }} ↗</a></div><div class="category-list">@forelse($categories as $category)<a href="{{ route('customer.shop', ['category' => $category->slug]) }}"><span>{{ $category->name }}</span><span aria-hidden="true">↗</span></a>@empty<a href="{{ route('customer.shop') }}">{{ __('Browse products') }} ↗</a>@endforelse</div></section>
<section class="shopping-guide">
    <p class="eyebrow">{{ __('From shelf to home') }}</p>
    <div>
        <h2>{{ __('Choose. Order. Collect.') }}</h2>
        <p>{{ __('Add your essentials to the cart, choose pickup or delivery at checkout, and follow your order from your account.') }}</p>
        @if(auth()->user()?->customer)<a href="{{ route('customer.history') }}">{{ __('My orders') }} ↗</a>@else<a href="{{ route('customer.shop') }}">{{ __('Browse products') }} ↗</a>@endif
    </div>
</section>
@endsection
