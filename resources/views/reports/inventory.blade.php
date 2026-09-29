@extends('layouts.app')
@section('title', 'Inventory Report')
@section('header', 'Inventory Report')

@section('content')
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Total Products</p>
            <p class="text-2xl font-bold text-gray-800">{{ $products->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Low Stock</p>
            <p class="text-2xl font-bold text-orange-500">{{ $lowStock->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Out of Stock</p>
            <p class="text-2xl font-bold text-red-500">{{ $outOfStock->count() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold">Stock Levels</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3">Product</th>
                    <th class="text-left px-4 py-3">Category</th>
                    <th class="text-right px-4 py-3">Min</th>
                    <th class="text-right px-4 py-3">Current</th>
                    <th class="text-left px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">{{ $product->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $product->category->name }}</td>
                    <td class="px-4 py-3 text-right">{{ $product->minimum_stock }}</td>
                    <td class="px-4 py-3 text-right font-bold {{ $product->current_stock <= 0 ? 'text-red-600' : ($product->current_stock <= $product->minimum_stock ? 'text-orange-500' : '') }}">
                        {{ $product->current_stock }}
                    </td>
                    <td class="px-4 py-3">
                        @if($product->current_stock <= 0)
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">Out of Stock</span>
                        @elseif($product->current_stock <= $product->minimum_stock)
                            <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-700">Low Stock</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">In Stock</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
