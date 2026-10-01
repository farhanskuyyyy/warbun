@extends('layouts.app')
@section('title', __('Units'))
@section('header', __('Units'))

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ __('Manage product units of measurement') }}</p>
        <a href="{{ route('units.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">+ Add Unit</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Name') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Symbol') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Status') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $unit->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $unit->symbol ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $unit->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $unit->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('units.edit', $unit) }}" class="text-primary hover:text-primary-dark mr-2">{{ __('Edit') }}</a>
                        <form action="{{ route('units.destroy', $unit) }}" method="POST" class="inline" onsubmit="return confirm(@js(__('Delete?')))">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">{{ __('No units found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $units->links() }}</div>
</div>
@endsection
