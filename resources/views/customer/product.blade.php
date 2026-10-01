@extends('layouts.customer')
@section('title', $product->name)

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="aspect-square bg-gray-100 rounded-xl flex items-center justify-center">
        @if($product->image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">@else<div class="product-placeholder w-full h-full flex items-center justify-center"><span>{{ $product->name }}</span></div>@endif
    </div>
    <div>
        <a href="{{ route('customer.shop') }}" class="text-sm text-primary hover:underline mb-2 inline-block">{{ __('← Kembali ke Toko') }}</a>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $product->name }}</h1>
        <p class="text-sm text-gray-500 mb-4">{{ $product->category->name }} {{ $product->brand ? '• ' . $product->brand->name : '' }}</p>
        <p class="text-3xl font-bold text-primary mb-4">{{ \App\Support\Money::format($product->selling_price) }}</p>
        <p class="text-sm {{ $product->current_stock > 0 ? 'text-green-600' : 'text-red-500' }} mb-4">
            {{ $product->current_stock > 0 ? __('In Stock').' (' . $product->current_stock . ' ' . $product->unit->symbol . ')' : __('Out of stock') }}
        </p>
        @if($product->description)
            <div class="prose prose-sm text-gray-600 mb-6">
                <p>{{ $product->description }}</p>
            </div>
        @endif
        @if($product->current_stock > 0)
        <button data-product="{{ json_encode(['id'=>$product->id,'name'=>$product->name,'price'=>$product->selling_price]) }}" onclick="const p=JSON.parse(this.dataset.product); addToCartAndGo(p.id,p.name,p.price)"
            class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary-dark transition">
            {{ __('Add to cart') }}
        </button>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function addToCartAndGo(id, name, price) {
    let cart;
    try { cart = JSON.parse(localStorage.getItem('warbun_cart') || '[]'); if (!Array.isArray(cart)) cart = []; } catch { cart = []; }
    const existing = cart.find(i => i.id === id);
    if (existing) { existing.quantity++; } else {
        cart.push({ id, name, price, quantity: 1 });
    }
    localStorage.setItem('warbun_cart', JSON.stringify(cart));
    window.location.href = '{{ route("customer.cart") }}';
}
</script>
@endpush
