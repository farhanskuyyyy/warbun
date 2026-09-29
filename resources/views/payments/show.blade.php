@extends('layouts.app')
@section('title', 'Payment Details')
@section('header', 'Payment ' . $payment->payment_number)

@section('content')
<div class="max-w-xl space-y-4">
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold">{{ $payment->payment_number }}</h2>
                <p class="text-gray-500">{{ $payment->created_at->format('d M Y H:i') }}</p>
            </div>
            <a href="{{ route('payments.index') }}" class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm">Back</a>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <p class="text-xs text-gray-500 uppercase">Amount</p>
                <p class="text-2xl font-bold text-primary">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Method</p>
                <p class="text-lg font-medium">{{ ucfirst($payment->method) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Status</p>
                @php $statusColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'paid' => 'bg-green-100 text-green-700', 'refunded' => 'bg-red-100 text-red-700']; @endphp
                <span class="px-3 py-1 text-sm rounded-full {{ $statusColors[$payment->status] ?? '' }}">{{ ucfirst($payment->status) }}</span>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Customer</p>
                <p class="font-medium">{{ $payment->customer?->name ?? '-' }}</p>
            </div>
        </div>

        @if($payment->reference_number)
            <div class="mb-4">
                <p class="text-xs text-gray-500 uppercase">Reference</p>
                <p class="font-mono">{{ $payment->reference_number }}</p>
            </div>
        @endif

        @if($payment->notes)
            <div class="mb-4">
                <p class="text-xs text-gray-500 uppercase">Notes</p>
                <p>{{ $payment->notes }}</p>
            </div>
        @endif

        @if($payment->paid_at)
            <div>
                <p class="text-xs text-gray-500 uppercase">Paid At</p>
                <p>{{ $payment->paid_at->format('d M Y H:i') }}</p>
            </div>
        @endif
    </div>

    @if($payment->status === 'pending')
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <h3 class="font-semibold mb-4">Actions</h3>
        <div class="flex gap-2">
            <form action="{{ route('payments.confirm', $payment) }}" method="POST" onsubmit="return confirm('Confirm this payment?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">✓ Confirm Payment</button>
            </form>
        </div>
    </div>
    @endif

    @if($payment->status === 'paid')
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <h3 class="font-semibold mb-4">Refund</h3>
        <form action="{{ route('payments.refund', $payment) }}" method="POST" onsubmit="return confirm('Refund this payment?')">
            @csrf
            <input type="text" name="reason" placeholder="Reason for refund" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm mb-2">
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">💸 Refund Payment</button>
        </form>
    </div>
    @endif
</div>
@endsection
