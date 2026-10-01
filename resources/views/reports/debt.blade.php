@extends('layouts.app')
@section('title', __('Debt Report'))
@section('header', __('Debt Report'))

@section('content')
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Total Outstanding') }}</p>
            <p class="text-2xl font-bold text-orange-500">{{ \App\Support\Money::format($totalOutstanding) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Total Collected') }}</p>
            <p class="text-2xl font-bold text-green-600">{{ \App\Support\Money::format($totalPaid) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Overdue') }}</p>
            <p class="text-2xl font-bold text-red-500">{{ \App\Support\Money::format($overdueAmount) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold">{{ __('Customers with Outstanding Debt') }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3">{{ __('Customer') }}</th>
                    <th class="text-right px-4 py-3">{{ __('Credit Limit') }}</th>
                    <th class="text-right px-4 py-3">{{ __('Outstanding') }}</th>
                    <th class="text-left px-4 py-3">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customersWithDebt as $customer)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $customer->name }}</td>
                    <td class="px-4 py-3 text-right">{{ \App\Support\Money::format($customer->debtAccount?->credit_limit ?? 0) }}</td>
                    <td class="px-4 py-3 text-right font-bold text-orange-500">{{ \App\Support\Money::format($customer->outstanding_balance) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $customer->debt_status === 'eligible' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ __('status.'.$customer->debt_status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">{{ __('No outstanding debt') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<section class="panel mt-5"><h2 class="font-semibold mb-3">{{ __('Debt aging') }}</h2><dl>@foreach($aging as $label=>$value)<div class="flex flex-wrap justify-between gap-3 py-2"><dt>{{ __($label) }}</dt><dd>{{ \App\Support\Money::format(\App\Support\Money::decimal($value)) }}</dd></div>@endforeach</dl></section>
@endsection
