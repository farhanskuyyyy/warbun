@extends('layouts.app')

@section('title', __('Categories'))
@section('header', __('Product Categories'))

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ __('Manage your product categories') }}</p>
        </div>
        <a href="{{ route('categories.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">
            + Add Category
        </a>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search categories...') }}"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">
                Search
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Name') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Slug') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Products') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Status') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $category->slug }}</td>
                    <td class="px-4 py-3">{{ $category->products_count ?? $category->products()->count() }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <x-crud-action action="edit" :href="route('categories.edit', $category)" />
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm(@js(__('Are you sure?')))">
                                @csrf
                                @method('DELETE')
                                <x-crud-action action="delete" />
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('No categories found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
        {{ $categories->links() }}
    </div>
</div>
@endsection
