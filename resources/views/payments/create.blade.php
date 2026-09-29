@extends('layouts.app')
@section('title', 'Record Payment')
@section('header', 'Record Payment')

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <form action="{{ route('payments.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sale *</label>
                    <select name="sale_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Select Sale (Debt)</option>
                        @foreach($sales as $sale)
                            <option value="{{ $sale->id }}">{{ $sale->sale_number }} - Rp {{ number_format($sale->debt_amount, 0, ',', '.') }} ({{ $sale->customer?->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (Rp) *</label>
                    <input type="number" name="amount" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Method *</label>
                    <select name="method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="qr">QR Payment</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                    <input type="text" name="reference_number" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Transfer reference, etc.">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <a href="{{ route('payments.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">Record Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection
