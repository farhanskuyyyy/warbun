@extends('layouts.app')
@section('title', __('Settings'))
@section('header', __('Store settings'))
@section('content')
<form method="POST" action="{{ route('settings.update') }}" class="panel max-w-xl space-y-4">@csrf @method('PUT')
    <label class="field">{{ __('Store name') }}<input name="store_name" value="{{ old('store_name',$settings['store_name']??'Warbun') }}" required></label>
    <label class="field">{{ __('Default debt term (days)') }}<input name="debt_terms" type="number" min="1" max="365" value="{{ old('debt_terms',$settings['debt_terms']??14) }}" required></label>
    <label class="field">{{ __('Delivery charge') }}<input name="shipping_cost" type="number" min="0" step="0.01" value="{{ old('shipping_cost',$settings['shipping_cost']??10000) }}" required></label>
    <label class="field">{{ __('Store contact (optional)') }}<input name="store_contact" maxlength="200" value="{{ old('store_contact', $settings['store_contact'] ?? '') }}"></label>
    <label class="field">{{ __('Payment instructions (optional)') }}<textarea name="payment_instructions" maxlength="2000" rows="4">{{ old('payment_instructions', $settings['payment_instructions'] ?? '') }}</textarea><span class="text-sm text-gray-600">{{ __('Shown to customers at checkout and on their order details.') }}</span></label>
    @can('settings.update')<button class="button-primary">{{ __('Save settings') }}</button>@endcan
</form>
@endsection
