@extends('layouts.customer')
@section('title', 'Shop')

@section('content')
<!-- Search & Filter -->
<div class="mb-6">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..."
            class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
        <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-primary-dark">Cari</button>
    </form>
    <div class="flex gap-2 mt-3 overflow-x-auto pb-2">
        <a href="{{ route('customer.shop') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ !request('category') ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-gray-600' }}">Semua</a>
        @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
            <a href="{{ route('customer.shop', ['category' => $cat->slug]) }}" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ request('category') == $cat->slug ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-gray-600' }}">{{ $cat->name }}</a>
        @endforeach
    </div>
</div>

<!-- Products Grid -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
    @forelse($products as $product)
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition">
        <a href="{{ route('customer.product', $product) }}">
            <div class="aspect-square bg-gray-100 flex items-center justify-center">
                <span class="text-4xl">📦</span>
            </div>
        </a>
        <div class="p-3">
            <a href="{{ route('customer.product', $product) }}">
                <h3 class="font-medium text-sm text-gray-800 line-clamp-2 hover:text-primary">{{ $product->name }}</h3>
            </a>
            <p class="text-primary font-bold mt-1">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
            <p class="text-xs {{ $product->current_stock > 0 ? 'text-green-500' : 'text-red-500' }} mt-1">
                {{ $product->current_stock > 0 ? 'Stok: ' . $product->current_stock : 'Stok Habis' }}
            </p>
            @if($product->current_stock > 0)
            <button onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }})" 
                class="w-full mt-2 py-2 bg-primary/10 text-primary rounded-lg text-sm font-medium hover:bg-primary hover:text-white transition">
                + Keranjang
            </button>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-12 text-gray-500">
        <p class="text-4xl mb-2">🔍</p>
        <p>Produk tidak ditemukan</p>
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
    let cart = JSON.parse(localStorage.getItem('warbun_cart') || '[]');
    const existing = cart.find(i => i.id === productId);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ id: productId, name, price, quantity: 1 });
    }
    localStorage.setItem('warbun_cart', JSON.stringify(cart));
    alert(name + ' ditambahkan ke keranjang!');
}
</script>
@endpush
