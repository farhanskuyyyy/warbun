@extends('layouts.customer')
@section('title', __('Shop'))

@section('content')
<div class="catalog-heading"><p class="eyebrow">{{ __('Your everyday essentials') }}</p><h1>{{ __('Shop') }}</h1><p>{{ __('Find what you need') }}</p></div>
<div class="mb-6">
    <form method="GET" class="flex gap-2">
        <input aria-label="{{ __('Search products') }}" type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari produk...') }}"
            class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
        <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-primary-dark">{{ __('Cari') }}</button>
    </form>
    <div class="flex gap-2 mt-3 overflow-x-auto pb-2">
        <a href="{{ route('customer.shop') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ !request('category') ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-gray-600' }}">{{ __('Semua') }}</a>
        @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
            <a href="{{ route('customer.shop', ['category' => $cat->slug]) }}" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ request('category') == $cat->slug ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-gray-600' }}">{{ $cat->name }}</a>
        @endforeach
    </div>
</div>

<p id="cart-notice" role="status" aria-live="polite" class="text-primary mb-4"></p>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
    @forelse($products as $product)
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition">
        <a href="{{ route('customer.product', $product) }}">
            <div class="aspect-square bg-gray-100 flex items-center justify-center">
                @if($product->image)<img loading="lazy" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">@else<div class="product-placeholder w-full h-full flex items-center justify-center"><span>{{ $product->name }}</span></div>@endif
            </div>
        </a>
        <div class="p-3">
            <a href="{{ route('customer.product', $product) }}">
                <h2 class="font-medium text-sm text-gray-800 line-clamp-2 hover:text-primary">{{ $product->name }}</h2>
            </a>
            <p class="text-primary font-bold mt-1">{{ \App\Support\Money::format($product->selling_price) }}</p>
            <p class="text-xs {{ $product->current_stock > 0 ? 'text-green-500' : 'text-red-500' }} mt-1">
                {{ $product->current_stock > 0 ? __('Stock').': '.$product->current_stock : __('Out of stock') }}
            </p>
            @if($product->current_stock > 0)
            <button data-product="{{ json_encode(['id'=>$product->id,'name'=>$product->name,'price'=>$product->selling_price]) }}" onclick="const p=JSON.parse(this.dataset.product); addToCart(p.id,p.name,p.price)"
                class="w-full mt-2 py-2 bg-primary/10 text-primary rounded-lg text-sm font-medium hover:bg-primary hover:text-white transition">
                {{ __('Add to cart') }}
            </button>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-12 text-gray-500">
        <p>{{ __('Produk tidak ditemukan') }}</p>
        <a class="inline-flex items-center min-h-[44px] text-primary underline mt-3" href="{{ route('customer.shop') }}">{{ __('View all products') }}</a>
    </div>
    @endforelse
</div>

<div class="mt-6 flex justify-center">
    {{ $products->withQueryString()->links() }}
</div>
@endsection

@push('scripts')
<script>
function addToCart(productId, name, price) {
    let cart;
    try { cart = JSON.parse(localStorage.getItem('warbun_cart') || '[]'); if (!Array.isArray(cart)) cart = []; } catch { cart = []; }
    const existing = cart.find(i => i.id === productId);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ id: productId, name, price, quantity: 1 });
    }
    localStorage.setItem('warbun_cart', JSON.stringify(cart));
    window.dispatchEvent(new Event('warbun:cartchange'));
    document.getElementById('cart-notice').textContent = @json(__('Added to cart'))+': '+name;
}
</script>
@endpush
