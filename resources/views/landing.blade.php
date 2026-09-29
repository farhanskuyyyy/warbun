@extends('layouts.customer')
@section('title', 'Home')

@section('content')
<!-- Hero -->
<div class="text-center py-12">
    <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">🏪 Warbun</h1>
    <p class="text-gray-500 mb-6 max-w-md mx-auto">Belanja kebutuhan sehari-hari dengan mudah. Pesan online atau ambil di tempat.</p>
    <a href="{{ route('customer.shop') }}" class="inline-block bg-primary text-white px-8 py-3 rounded-xl font-semibold hover:bg-primary-dark transition">
        Mulai Belanja →
    </a>
</div>

<!-- Features -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-12">
    <div class="bg-white rounded-xl p-6 border border-gray-100 text-center">
        <div class="text-3xl mb-3">🛒</div>
        <h3 class="font-semibold mb-1">Belanja Online</h3>
        <p class="text-sm text-gray-500">Pilih produk, pesan, dan terima di rumah</p>
    </div>
    <div class="bg-white rounded-xl p-6 border border-gray-100 text-center">
        <div class="text-3xl mb-3">🏪</div>
        <h3 class="font-semibold mb-1">Ambil di Tempat</h3>
        <p class="text-sm text-gray-500">Pesan online, ambil langsung di warung</p>
    </div>
    <div class="bg-white rounded-xl p-6 border border-gray-100 text-center">
        <div class="text-3xl mb-3">💳</div>
        <h3 class="font-semibold mb-1">Bayar Nanti</h3>
        <p class="text-sm text-gray-500">Beli sekarang, bayar belakangan</p>
    </div>
</div>

<!-- Featured Products -->
<h2 class="text-xl font-bold text-gray-800 mb-4">Produk Unggulan</h2>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @foreach(\App\Models\Product::where('is_active', true)->where('is_featured', true)->take(8)->get() as $product)
    <a href="{{ route('customer.product', $product) }}" class="bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition">
        <div class="aspect-square bg-gray-100 flex items-center justify-center">
            <span class="text-4xl">📦</span>
        </div>
        <div class="p-3">
            <h3 class="font-medium text-sm text-gray-800 truncate">{{ $product->name }}</h3>
            <p class="text-primary font-bold text-sm">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Stok: {{ $product->current_stock }}</p>
        </div>
    </a>
    @endforeach
</div>
@endsection
