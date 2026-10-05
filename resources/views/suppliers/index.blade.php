@extends('layouts.app')
@section('title', __('Suppliers'))
@section('header', __('Suppliers'))

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ __('Manage your suppliers') }}</p>
        <a href="{{ route('suppliers.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">+ Add Supplier</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Name') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Contact') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Phone') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Status') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $supplier->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $supplier->contact_person ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $supplier->phone ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $supplier->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <x-crud-action action="edit" :href="route('suppliers.edit', $supplier)" />
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline" onsubmit="return confirm(@js(__('Delete?')))">
                            @csrf @method('DELETE')
                            <x-crud-action action="delete" />
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('No suppliers found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $suppliers->links() }}</div>
</div>
@endsection
