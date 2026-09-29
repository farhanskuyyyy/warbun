@extends('layouts.app')
@section('title', 'Payments')
@section('header', 'Payment Management')

@section('content')
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Total Paid</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Total Pending</p>
            <p class="text-2xl font-bold text-yellow-500">Rp {{ number_format($totalPending, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('payments.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">+ Record Payment</a>
    </div>

    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <form method="GET" class="flex gap-2 flex-wrap">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
            <select name="method" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">All Methods</option>
                <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="transfer" {{ request('method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                <option value="ewallet" {{ request('method') == 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                <option value="qr" {{ request('method') == 'qr' ? 'selected' : '' }}>QR</option>
                <option value="online" {{ request('method') == 'online' ? 'selected' : '' }}>Online</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Payment #</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Customer</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Method</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $payment->payment_number }}</td>
                    <td class="px-4 py-3">{{ $payment->customer?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">{{ ucfirst($payment->method) }}</td>
                    <td class="px-4 py-3">
                        @php $statusColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'paid' => 'bg-green-100 text-green-700', 'refunded' => 'bg-red-100 text-red-700']; @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$payment->status] ?? '' }}">{{ $payment->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $payment->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('payments.show', $payment) }}" class="text-primary hover:text-primary-dark">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No payments found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $payments->withQueryString()->links() }}</div>
</div>
@endsection
