@extends('layouts.app')

@section('title', __('Products'))
@section('header', __('Products'))

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ __('Manage your product catalog') }}</p>
        <a href="{{ route('products.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">
            + Add Product
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <form method="GET" class="flex gap-2 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name, SKU, barcode...') }}"
                class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
            <select name="category_id" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('All Categories') }}</option>
                @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('All Status') }}</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">{{ __('Filter') }}</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Product') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('SKU') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Category') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Cost') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Price') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Stock') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Status') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $product->name }}</div>
                        @if($product->brand)
                            <div class="text-xs text-gray-500">{{ $product->brand->name }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $product->sku }}</td>
                    <td class="px-4 py-3">{{ $product->category->name }}</td>
                    <td class="px-4 py-3 text-right">{{ \App\Support\Money::format($product->cost_price) }}</td>
                    <td class="px-4 py-3 text-right font-medium">{{ \App\Support\Money::format($product->selling_price) }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="{{ $product->isOutOfStock() ? 'text-red-600 font-bold' : ($product->isLowStock() ? 'text-orange-500' : '') }}">
                            {{ $product->current_stock }} {{ $product->unit->symbol ?? '' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('products.show', $product) }}" class="text-blue-500 hover:text-blue-700">{{ __('View') }}</a>
                            <a href="{{ route('products.edit', $product) }}" class="text-primary hover:text-primary-dark">{{ __('Edit') }}</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm(@js(__('Archive this product?')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">{{ __('No products found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-center">{{ $products->withQueryString()->links() }}</div>
</div>
@endsection
