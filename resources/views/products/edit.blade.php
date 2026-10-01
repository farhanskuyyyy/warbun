@extends('layouts.app')

@section('title', __('Edit Product'))
@section('header', __('Edit Product'))

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
        <label class="field">{{ __('Product image') }}<input type="file" name="image_upload" accept="image/jpeg,image/png,image/webp"></label>
            @method('PUT')
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="field-name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Product Name *') }}</label>
                        <input id="field-name" type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('name') border-red-500 @enderror">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="field-sku" class="block text-sm font-medium text-gray-700 mb-1">{{ __('SKU *') }}</label>
                        <input id="field-sku" type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('sku') border-red-500 @enderror">
                        @error('sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="field-barcode" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Barcode') }}</label>
                    <input id="field-barcode" type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" maxlength="50" autocomplete="off" data-barcode-field aria-describedby="barcode-help" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    <p id="barcode-help" class="text-sm text-gray-500 mt-2">{{ __('Scan the packaging barcode into this field or type it exactly, including leading zeroes. Each barcode belongs to one product.') }}</p>
                    @error('barcode') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="field-category_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category *') }}</label>
                        <select id="field-category_id" name="category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="field-product_type_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Type *') }}</label>
                        <select id="field-product_type_id" name="product_type_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            @foreach($productTypes as $type)
                                <option value="{{ $type->id }}" {{ old('product_type_id', $product->product_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="field-brand_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Brand') }}</label>
                        <select id="field-brand_id" name="brand_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="">{{ __('Select Brand') }}</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="field-unit_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Unit *') }}</label>
                        <select id="field-unit_id" name="unit_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->symbol }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="field-supplier_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Supplier') }}</label>
                        <select id="field-supplier_id" name="supplier_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="">{{ __('Select Supplier') }}</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="field-cost_price" class="block text-sm font-medium text-gray-700 mb-1">Cost Price (Rp) *</label>
                        <input id="field-cost_price" type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label for="field-selling_price" class="block text-sm font-medium text-gray-700 mb-1">Selling Price (Rp) *</label>
                        <input id="field-selling_price" type="number" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="field-current_stock" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Current Stock *') }}</label>
                        <input id="field-current_stock" type="number" disabled value="{{ old('current_stock', $product->current_stock) }}" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label for="field-minimum_stock" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Minimum Stock *') }}</label>
                        <input id="field-minimum_stock" type="number" name="minimum_stock" value="{{ old('minimum_stock', $product->minimum_stock) }}" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label for="field-description" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                    <textarea id="field-description" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="flex gap-6">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="w-4 h-4 text-primary rounded">
                        <span class="text-sm">{{ __('Active') }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_available_online" value="1" {{ old('is_available_online', $product->is_available_online) ? 'checked' : '' }} class="w-4 h-4 text-primary rounded">
                        <span class="text-sm">{{ __('Available Online') }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="w-4 h-4 text-primary rounded">
                        <span class="text-sm">{{ __('Featured') }}</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-2 mt-6 pt-4 border-t border-gray-200">
                <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">{{ __('Cancel') }}</a>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">{{ __('Update Product') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
