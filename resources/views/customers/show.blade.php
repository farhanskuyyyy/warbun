@extends('layouts.app')
@section('title', 'Customer Details')
@section('header', $customer->name)

@section('content')
<div class="space-y-4">
    <!-- Customer Info -->
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold">{{ $customer->name }}</h2>
                <p class="text-gray-500">{{ $customer->phone }} {{ $customer->email ? '| ' . $customer->email : '' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('customers.edit', $customer) }}" class="px-3 py-1 bg-primary text-white rounded text-sm">Edit</a>
                <a href="{{ route('customers.index') }}" class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm">Back</a>
            </div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500 uppercase">Outstanding Balance</p>
                <p class="text-lg font-bold {{ $customer->outstanding_balance > 0 ? 'text-orange-500' : 'text-green-600' }}">
                    Rp {{ number_format($customer->outstanding_balance, 0, ',', '.') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Credit Limit</p>
                <p class="text-lg font-bold">Rp {{ number_format($customer->debtAccount?->credit_limit ?? 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Can Use Debt</p>
                <p class="text-lg font-bold">{{ $customer->can_use_debt ? 'Yes' : 'No' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Status</p>
                <span class="px-2 py-1 text-xs rounded-full {{ $customer->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        @if($customer->address)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500 uppercase mb-1">Address</p>
                <p>{{ $customer->address }}</p>
            </div>
        @endif
    </div>

    <!-- Recent Sales -->
    <div class="bg-white rounded-xl border border-gray-100">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Recent Sales</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Sale #</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Total</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Payment</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales as $sale)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-3 font-medium">{{ $sale->sale_number }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ ucfirst($sale->payment_method) }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $sale->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-4 text-center text-gray-500">No sales yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Debt Transactions -->
    @if($customer->debtTransactions->count() > 0)
    <div class="bg-white rounded-xl border border-gray-100">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Debt History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Ref</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Debit</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Credit</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Balance</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($debtTransactions as $txn)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-3 font-mono text-xs">{{ $txn->reference_number }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $txn->type === 'payment' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ $txn->type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">{{ $txn->debit_amount > 0 ? 'Rp ' . number_format($txn->debit_amount, 0, ',', '.') : '-' }}</td>
                        <td class="px-4 py-3 text-right">{{ $txn->credit_amount > 0 ? 'Rp ' . number_format($txn->credit_amount, 0, ',', '.') : '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($txn->balance_after, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $txn->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
