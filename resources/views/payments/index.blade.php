@extends('layouts.app')
@section('title', __('Payments'))
@section('header', __('Payment Management'))

@section('content')
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Total Paid') }}</p>
            <p class="text-2xl font-bold text-green-600">{{ \App\Support\Money::format($totalPaid) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">{{ __('Total Pending') }}</p>
            <p class="text-2xl font-bold text-yellow-500">{{ \App\Support\Money::format($totalPending) }}</p>
        </div>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('payments.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">{{ __('+ Record Payment') }}</a>
    </div>

    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <form method="GET" class="flex gap-2 flex-wrap">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('All Status') }}</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>{{ __('Refunded') }}</option>
            </select>
            <select name="method" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('All Methods') }}</option>
                <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                <option value="transfer" {{ request('method') == 'transfer' ? 'selected' : '' }}>{{ __('Transfer') }}</option>
                <option value="ewallet" {{ request('method') == 'ewallet' ? 'selected' : '' }}>{{ __('E-Wallet') }}</option>
                <option value="qr" {{ request('method') == 'qr' ? 'selected' : '' }}>{{ __('QR') }}</option>
                <option value="online" {{ request('method') == 'online' ? 'selected' : '' }}>{{ __('Online') }}</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">{{ __('Filter') }}</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Payment #') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Customer') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Amount') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Method') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Status') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Date') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $payment->payment_number }}</td>
                    <td class="px-4 py-3">{{ $payment->customer?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-medium">{{ \App\Support\Money::format($payment->amount) }}</td>
                    <td class="px-4 py-3">{{ __('status.'.$payment->method) }}</td>
                    <td class="px-4 py-3">
                        @php $statusColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'paid' => 'bg-green-100 text-green-700', 'refunded' => 'bg-red-100 text-red-700']; @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$payment->status] ?? '' }}">{{ __('status.'.$payment->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $payment->created_at->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <x-crud-action action="view" :href="route('payments.show', $payment)" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ __('No payments found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $payments->withQueryString()->links() }}</div>
</div>
@endsection
