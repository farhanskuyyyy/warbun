@extends('layouts.app')
@section('title', __('New Debt'))
@section('header', __('New Debt Transaction'))

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <form action="{{ route('debt.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="field-customer_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Customer *') }}</label>
                    <select id="field-customer_id" name="customer_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">{{ __('Select Customer') }}</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (Credit: {{ \App\Support\Money::format($c->debtAccount?->credit_limit ?? 0) }})</option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="field-amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (Rp) *</label>
                    <input id="field-amount" type="number" name="amount" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="field-due_date" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Due Date *') }}</label>
                    <input id="field-due_date" type="date" name="due_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="field-description" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description *') }}</label>
                    <input id="field-description" type="text" name="description" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Purchase on credit">
                </div>
                <div>
                    <label for="field-notes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea id="field-notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <a href="{{ route('debt.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">{{ __('Cancel') }}</a>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">{{ __('Create Debt') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
