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
    <fieldset class="space-y-4"><legend>{{ __('Delivery area and customer service fees') }}</legend><p>{{ __('Configure agreed fees only. These settings do not activate a live payment provider.') }}</p><label class="field">{{ __('Radius policy') }}<select name="radius_enabled"><option value="0" @selected(($settings['radius_enabled']??'0')==='0')>{{ __('Fixed delivery charge') }}</option><option value="1" @selected(($settings['radius_enabled']??'0')==='1')>{{ __('Below 5km free; 5–20km Rp10.000; 20km unavailable') }}</option></select></label>@foreach(['store_latitude'=>['Store latitude','-90','90'],'store_longitude'=>['Store longitude','-180','180'],'qris_fee_bps'=>['QRIS fee (basis points; 70 = 0.7%)','0','10000'],'payment_promo_bps'=>['Payment promotion (basis points)','0','10000'],'payment_promo_flat'=>['Payment promotion flat discount','0','1000000'],'payment_promo_cap'=>['Promotion cap (0 means no cap)','0','1000000'],'ewallet_fee'=>['E-wallet flat customer fee','0','1000000']] as $key=>[$label,$min,$max])<label class="field">{{ __($label) }}<input name="{{ $key }}" type="number" min="{{ $min }}" max="{{ $max }}" step="{{ str_starts_with($key,'store_') ? '0.0000001' : '1' }}" value="{{ old($key,$settings[$key]??(str_starts_with($key,'store_') ? '' : '0')) }}"></label>@endforeach</fieldset>
    @can('settings.update')<button class="button-primary">{{ __('Save settings') }}</button>@endcan
</form>
@endsection
