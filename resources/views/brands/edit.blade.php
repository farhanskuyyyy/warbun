@extends('layouts.app')
@section('title', isset($brand) ? 'Edit Brand' : 'Create Brand')
@section('header', isset($brand) ? 'Edit Brand' : 'Create Brand')

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <form action="{{ isset($brand) ? route('brands.update', $brand) : route('brands.store') }}" method="POST">
            @csrf
            @if(isset($brand)) @method('PUT') @endif
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $brand->name ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                    <input type="text" name="code" value="{{ old('code', $brand->code ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('description', $brand->description ?? '') }}</textarea>
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $brand->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 text-primary rounded">
                    <span class="text-sm">Active</span>
                </label>
            </div>

            <div class="flex gap-2 mt-6">
                <a href="{{ route('brands.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
