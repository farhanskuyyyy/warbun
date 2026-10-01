@extends('layouts.app')
@section('title', __('Debt Management'))
@section('header', __('Debt / Accounts Receivable'))

@section('content')
<div class="space-y-4">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Total Outstanding') }}</p>
            <p class="text-2xl font-bold text-orange-500">{{ \App\Support\Money::format($totalOutstanding) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Overdue') }}</p>
            <p class="text-2xl font-bold text-red-500">{{ \App\Support\Money::format($totalOverdue) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Customers with Debt') }}</p>
            <p class="text-2xl font-bold text-gray-800">{{ $customersWithDebt }}</p>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-2">
        <a href="{{ route('debt.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">
            + New Debt
        </a>
        <button onclick="document.getElementById('paymentModal').showModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
            💳 Record Payment
        </button>
    </div>

    <!-- Transactions -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">{{ __('Debt Transactions') }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Ref') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Customer') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Type') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Debit') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Credit') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Balance') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Due') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Status') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-mono text-xs">{{ $txn->reference_number }}</td>
                    <td class="px-4 py-3">{{ $txn->customer->name }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $txn->type === 'payment' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ __('status.'.$txn->type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">{{ $txn->debit_amount > 0 ? \App\Support\Money::format($txn->debit_amount) : '-' }}</td>
                    <td class="px-4 py-3 text-right">{{ $txn->credit_amount > 0 ? \App\Support\Money::format($txn->credit_amount) : '-' }}</td>
                    <td class="px-4 py-3 text-right font-medium">{{ \App\Support\Money::format($txn->balance_after) }}</td>
                    <td class="px-4 py-3 {{ $txn->isOverdue() ? 'text-red-500 font-medium' : 'text-gray-500' }}">
                        {{ $txn->due_date ? $txn->due_date->translatedFormat('d M Y') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        @php $statusColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'paid' => 'bg-green-100 text-green-700', 'overdue' => 'bg-red-100 text-red-700']; @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$txn->status] ?? 'bg-gray-100 text-gray-700' }}">{{ __('status.'.$txn->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $txn->created_at->translatedFormat('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">{{ __('No debt transactions found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $transactions->withQueryString()->links() }}</div>
</div>

<!-- Payment Modal -->
<dialog id="paymentModal" aria-labelledby="paymentModal-title" class="w-full max-w-md rounded-xl p-0">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 id="paymentModal-title" class="text-lg font-semibold mb-4">{{ __('Record Debt Payment') }}</h3>
        <form action="{{ route('debt.payment') }}" method="POST">
            @csrf
            <input type="hidden" name="request_key" value="{{ old('request_key', (string) \Illuminate\Support\Str::uuid()) }}">
            <div class="space-y-4">
                <div>
                    <label for="paymentModal-customer_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Customer *') }}</label>
                    <select id="paymentModal-customer_id" name="customer_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">{{ __('Select Customer') }}</option>
                        @foreach(\App\Models\Customer::where('outstanding_balance', '>', 0)->get() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ __('Outstanding') }}: {{ \App\Support\Money::format($c->outstanding_balance) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="paymentModal-amount" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Amount (Rp) *') }}</label>
                    <input id="paymentModal-amount" type="number" name="amount" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="paymentModal-payment_method" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Payment Method *') }}</label>
                    <select id="paymentModal-payment_method" name="payment_method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="transfer">{{ __('Transfer') }}</option>
                        <option value="ewallet">{{ __('E-Wallet') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                </div>
                <div>
                    <label for="paymentModal-notes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea id="paymentModal-notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('paymentModal').close()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">{{ __('Cancel') }}</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg font-medium">{{ __('Submit') }}</button>
            </div>
        </form>
    </div>
</dialog>
@if(auth()->user()->can('debt.adjust') || auth()->user()->can('debt.write-off'))
<form method="POST" action="{{ route('debt.correction') }}" class="panel mt-5 space-y-3">@csrf<h2 class="font-semibold">{{ __('Debt correction') }}</h2><label class="field">{{ __('Customer') }}<select name="customer_id" required>@foreach(\App\Models\Customer::where('outstanding_balance','>',0)->get() as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label><label class="field">{{ __('Type') }}<select name="type">@can('debt.adjust')<option value="adjustment">{{ __('status.adjustment') }}</option>@endcan @can('debt.write-off')<option value="write_off">{{ __('status.write_off') }}</option>@endcan</select></label><label class="field">{{ __('Amount') }}<input name="amount" type="number" min="0.01" step="0.01" required></label><label class="field">{{ __('Reason') }}<input name="reason" maxlength="255" required></label><button class="button-primary">{{ __('Record correction') }}</button></form>
@endif
@endsection
