@extends('layouts.app')
@section('title', __('Create Customer'))
@section('header', __('Create Customer'))

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="field-name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name *') }}</label>
                    <input id="field-name" type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="field-phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
                        <input id="field-phone" type="text" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label for="field-email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                        <input id="field-email" type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                <div>
                    <label for="field-address" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }}</label>
                    <textarea id="field-address" name="address" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('address') }}</textarea>
                </div>
                <div class="border-t border-gray-200 pt-4">
                    <label class="flex items-center gap-2 mb-2">
                        <input type="checkbox" name="can_use_debt" value="1" {{ old('can_use_debt') ? 'checked' : '' }} class="w-4 h-4 text-primary rounded">
                        <span class="text-sm font-medium">{{ __('Allow Debt Purchases') }}</span>
                    </label>
                    <div id="creditLimitField" class="{{ old('can_use_debt') ? '' : 'hidden' }}">
                        <label for="field-credit_limit" class="block text-sm font-medium text-gray-700 mb-1">Credit Limit (Rp)</label>
                        <input id="field-credit_limit" type="number" name="credit_limit" value="{{ old('credit_limit', 0) }}" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <a href="{{ route('customers.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">{{ __('Cancel') }}</a>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark">{{ __('Save Customer') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelector('[name="can_use_debt"]').addEventListener('change', function() {
    document.getElementById('creditLimitField').classList.toggle('hidden', !this.checked);
});
</script>
@endsection
