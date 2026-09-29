@extends('layouts.app')
@section('title', 'Debt Report')
@section('header', 'Debt Report')

@section('content')
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Total Outstanding</p>
            <p class="text-2xl font-bold text-orange-500">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Total Collected</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Overdue</p>
            <p class="text-2xl font-bold text-red-500">Rp {{ number_format($overdueAmount, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold">Customers with Outstanding Debt</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3">Customer</th>
                    <th class="text-right px-4 py-3">Credit Limit</th>
                    <th class="text-right px-4 py-3">Outstanding</th>
                    <th class="text-left px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customersWithDebt as $customer)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $customer->name }}</td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format($customer->debtAccount?->credit_limit ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-bold text-orange-500">Rp {{ number_format($customer->outstanding_balance, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $customer->debt_status === 'eligible' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $customer->debt_status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No outstanding debt</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

echo "✅ Report views created"
