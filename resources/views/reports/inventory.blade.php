@extends('layouts.app')
@section('title', __('Inventory Report'))
@section('header', __('Inventory Report'))

@section('content')
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Total Products') }}</p>
            <p class="text-2xl font-bold text-gray-800">{{ $products->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Low Stock') }}</p>
            <p class="text-2xl font-bold text-orange-500">{{ $lowStock->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Out of Stock') }}</p>
            <p class="text-2xl font-bold text-red-500">{{ $outOfStock->count() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold">{{ __('Stock Levels') }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3">{{ __('Product') }}</th>
                    <th class="text-left px-4 py-3">{{ __('Category') }}</th>
                    <th class="text-right px-4 py-3">{{ __('Min') }}</th>
                    <th class="text-right px-4 py-3">{{ __('Current') }}</th>
                    <th class="text-left px-4 py-3">{{ __('Status') }}</th>
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
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">{{ __('Out of Stock') }}</span>
                        @elseif($product->current_stock <= $product->minimum_stock)
                            <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-700">{{ __('Low Stock') }}</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">{{ __('In Stock') }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<section class="panel mt-5"><h2 class="font-semibold mb-3">{{ __('Best-selling products') }}</h2><p class="text-sm mb-3">{{ __('Units sold after returns') }}</p><ul>@forelse($bestSelling as $product)<li>{{ $product->name }} · {{ $product->net_quantity }}</li>@empty<li>{{ __('No transactions yet') }}</li>@endforelse</ul><h2 class="font-semibold mt-5 mb-3">{{ __('No recorded sales') }}</h2><p>{{ $slowMoving->pluck('name')->join(', ') ?: '—' }}</p></section>
@endsection
