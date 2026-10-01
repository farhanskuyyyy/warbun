@extends('layouts.app')
@section('title', __('Customers'))
@section('header', __('Customer Management'))

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ __('Manage your customers') }}</p>
        <a href="{{ route('customers.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">+ Add Customer</a>
    </div>

    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <form method="GET" class="flex gap-2 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name, phone, email...') }}"
                class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('All Status') }}</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </select>
            <select name="has_debt" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('All Debt Status') }}</option>
                <option value="yes" {{ request('has_debt') == 'yes' ? 'selected' : '' }}>{{ __('Has Debt') }}</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">{{ __('Filter') }}</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Name') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Phone') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Email') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Outstanding') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Status') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $customer->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $customer->phone ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $customer->email ?? '-' }}</td>
                    <td class="px-4 py-3 text-right {{ $customer->outstanding_balance > 0 ? 'text-orange-500 font-medium' : '' }}">
                        {{ \App\Support\Money::format($customer->outstanding_balance) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $customer->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('customers.show', $customer) }}" class="text-blue-500 hover:text-blue-700 mr-2">{{ __('View') }}</a>
                        <a href="{{ route('customers.edit', $customer) }}" class="text-primary hover:text-primary-dark mr-2">{{ __('Edit') }}</a>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm(@js(__('Delete?')))">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No customers found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $customers->withQueryString()->links() }}</div>
</div>
@endsection
