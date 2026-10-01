@extends('layouts.app')
@section('title', __('Record Payment'))
@section('header', __('Record Payment'))

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <form action="{{ route('payments.store') }}" method="POST">
            @csrf
            <input type="hidden" name="request_key" value="{{ old('request_key', (string) \Illuminate\Support\Str::uuid()) }}">
            <div class="space-y-4">
                <div>
                    <label for="field-sale_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Sale *') }}</label>
                    <select id="field-sale_id" name="sale_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Select Sale (Debt)</option>
                        @foreach($sales as $sale)
                            <option value="{{ $sale->id }}">{{ $sale->sale_number }} - {{ \App\Support\Money::format($sale->debt_amount) }} ({{ $sale->customer?->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="field-amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (Rp) *</label>
                    <input id="field-amount" type="number" name="amount" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="field-method" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Method *') }}</label>
                    <select id="field-method" name="method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="transfer">{{ __('Transfer') }}</option>
                        <option value="ewallet">{{ __('E-Wallet') }}</option>
                        <option value="qr">{{ __('QR Payment') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                </div>
                <div>
                    <label for="field-reference_number" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reference Number') }}</label>
                    <input id="field-reference_number" type="text" name="reference_number" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="{{ __('Transfer reference, etc.') }}">
                </div>
                <div>
                    <label for="field-notes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea id="field-notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <a href="{{ route('payments.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">{{ __('Cancel') }}</a>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">{{ __('Record Payment') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
