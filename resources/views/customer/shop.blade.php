@extends('layouts.customer')
@section('title', __('Shop'))
@section('content')
<div class="catalog-heading"><p class="eyebrow">{{ __('Your everyday essentials') }}</p><h1>{{ __('Find what you need') }}</h1><p>{{ __('Add your essentials, then review your cart and choose pickup or delivery.') }}</p></div>
<form method="GET" class="catalog-filters" role="search">
    <div class="field"><label for="product-search">{{ __('Search products') }}</label><input id="product-search" type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari produk...') }}"></div>
    <div class="field"><label for="category-filter">{{ __('Category') }}</label><select id="category-filter" name="category"><option value="">{{ __('All categories') }}</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>@endforeach</select></div>
    <button type="submit" class="button-primary">{{ __('Cari') }}</button>
</form>
<div class="catalog-results"><p>{{ trans_choice('Products found: :count', $products->total(), ['count' => $products->total()]) }}</p>@if(request('search') || request('category'))<a href="{{ route('customer.shop') }}">{{ __('Clear filters') }}</a>@endif</div>
<div class="catalog-view-switch" role="group" aria-label="{{ __('Product display') }}">
    <span>{{ __('Display') }}</span>
    <button type="button" data-catalog-view="grid" aria-pressed="true" aria-controls="shop-products">{{ __('Grid') }}</button>
    <button type="button" data-catalog-view="list" aria-pressed="false" aria-controls="shop-products">{{ __('List') }}</button>
</div>
<p id="cart-notice" class="cart-notice" role="status" aria-live="polite"></p>
<div class="catalog-grid" id="shop-products" data-view="grid">
@forelse($products as $product)
    <article class="catalog-product">
        <a class="catalog-product-image" href="{{ route('customer.product', $product) }}" aria-label="{{ $product->name }}">
            @if($product->image)<img loading="lazy" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}">@else<span class="product-type-placeholder">{{ $product->category?->name }}<span>{{ $product->unit?->symbol }}</span></span>@endif
        </a>
        <div class="catalog-product-info"><p class="product-category">{{ $product->category?->name }}</p><a href="{{ route('customer.product', $product) }}"><h2>{{ $product->name }}</h2></a><p class="product-price">{{ \App\Support\Money::format($product->selling_price) }} <span>/ {{ $product->unit?->symbol }}</span></p><p class="product-stock {{ $product->current_stock <= 0 ? 'text-red-500' : '' }}">{{ $product->current_stock > 0 ? __('Stock').': '.$product->current_stock : __('Out of stock') }}</p></div>
        <div class="catalog-product-action"><button type="button" data-product="{{ json_encode(['id' => $product->id, 'name' => $product->name, 'price' => $product->selling_price, 'stock' => $product->current_stock, 'unit' => $product->unit?->symbol]) }}" class="button-primary" @disabled($product->current_stock <= 0)>{{ $product->current_stock > 0 ? __('Add to cart') : __('Out of stock') }}</button><span class="product-cart-quantity" data-product-quantity="{{ $product->id }}"></span></div>
    </article>
@empty
    <div class="catalog-empty"><h2>{{ __('Produk tidak ditemukan') }}</h2><p>{{ __('Try a different name or choose another category.') }}</p><a class="button-primary" href="{{ route('customer.shop') }}">{{ __('View all products') }}</a></div>
@endforelse
</div>
<div class="catalog-pagination">{{ $products->withQueryString()->links() }}</div>
<aside class="shopping-cart-bar" data-cart-bar hidden aria-label="{{ __('Cart') }}"><div><strong data-cart-bar-count></strong><span data-cart-bar-subtotal></span></div><a href="{{ route('customer.cart') }}" class="button-primary">{{ __('Review cart') }} <span aria-hidden="true">→</span></a></aside>
@endsection
