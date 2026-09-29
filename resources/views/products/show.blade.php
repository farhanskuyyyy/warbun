@extends('layouts.app')

@section('title', 'Product Details')
@section('header', $product->name)

@section('content')
<div class="max-w-2xl space-y-4">
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold">{{ $product->name }}</h2>
                <p class="text-gray-500">SKU: {{ $product->sku }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('products.edit', $product) }}" class="px-3 py-1 bg-primary text-white rounded text-sm">Edit</a>
                <a href="{{ route('products.index') }}" class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm">Back</a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500 uppercase">Category</p>
                <p class="font-medium">{{ $product->category->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Type</p>
                <p class="font-medium">{{ $product->productType->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Brand</p>
                <p class="font-medium">{{ $product->brand?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Unit</p>
                <p class="font-medium">{{ $product->unit->name }} ({{ $product->unit->symbol }})</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Cost Price</p>
                <p class="font-medium">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Selling Price</p>
                <p class="font-medium text-primary">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Current Stock</p>
                <p class="font-medium {{ $product->isOutOfStock() ? 'text-red-600' : ($product->isLowStock() ? 'text-orange-500' : '') }}">
                    {{ $product->current_stock }} {{ $product->unit->symbol }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Minimum Stock</p>
                <p class="font-medium">{{ $product->minimum_stock }} {{ $product->unit->symbol }}</p>
            </div>
        </div>

        @if($product->description)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500 uppercase mb-1">Description</p>
                <p class="text-gray-700">{{ $product->description }}</p>
            </div>
        @endif

        <div class="mt-4 pt-4 border-t border-gray-200 flex gap-4">
            <span class="px-3 py-1 text-xs rounded-full {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $product->is_active ? 'Active' : 'Inactive' }}
            </span>
            @if($product->is_available_online)
                <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-700">Online</span>
            @endif
            @if($product->is_featured)
                <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">Featured</span>
            @endif
        </div>
    </div>
</div>
@endsection
