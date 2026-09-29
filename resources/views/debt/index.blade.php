@extends('layouts.app')
@section('title', 'Debt Management')
@section('header', 'Debt / Accounts Receivable')

@section('content')
<div class="space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Total Outstanding</p>
            <p class="text-2xl font-bold text-orange-500">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Overdue</p>
            <p class="text-2xl font-bold text-red-500">Rp {{ number_format($totalOverdue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Customers with Debt</p>
            <p class="text-2xl font-bold text-gray-800">{{ $customersWithDebt }}</p>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-2">
        <a href="{{ route('debt.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">
            + New Debt
        </a>
        <button onclick="document.getElementById('paymentModal').classList.remove('hidden')" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
            💳 Record Payment
        </button>
    </div>

    <!-- Transactions -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Debt Transactions</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Ref</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Customer</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Debit</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Credit</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Balance</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Due</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-mono text-xs">{{ $txn->reference_number }}</td>
                    <td class="px-4 py-3">{{ $txn->customer->name }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $txn->type === 'payment' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $txn->type }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">{{ $txn->debit_amount > 0 ? 'Rp ' . number_format($txn->debit_amount, 0, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right">{{ $txn->credit_amount > 0 ? 'Rp ' . number_format($txn->credit_amount, 0, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($txn->balance_after, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 {{ $txn->due_date && $txn->due_date->isPast() ? 'text-red-500 font-medium' : 'text-gray-500' }}">
                        {{ $txn->due_date ? $txn->due_date->format('d M Y') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        @php $statusColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'paid' => 'bg-green-100 text-green-700', 'overdue' => 'bg-red-100 text-red-700']; @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$txn->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $txn->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $txn->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">No debt transactions found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $transactions->withQueryString()->links() }}</div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold mb-4">Record Debt Payment</h3>
        <form action="{{ route('debt.payment') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                    <select name="customer_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Select Customer</option>
                        @foreach(Customer::where('outstanding_balance', '>', 0)->get() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (Outstanding: Rp {{ number_format($c->outstanding_balance, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (Rp) *</label>
                    <input type="number" name="amount" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg font-medium">Submit</button>
            </div>
        </form>
    </div>
</div>
@endsection
