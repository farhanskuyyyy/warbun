@props(['addressId', 'addressName', 'pointId'])
<div class="address-selector">
    <div class="address-selector-heading"><label for="shippingAddressId">{{ __('Send to') }}</label><button type="button" class="button-secondary" data-address-add>{{ __('Add address') }}</button></div>
    <select id="shippingAddressId" name="address_id" data-address-select data-initial="{{ old('address_id') }}"><option value="">{{ __('Choose an address') }}</option></select>
    <p class="checkout-help" data-address-status role="status"></p>
    <button type="button" class="button-secondary" data-address-retry hidden>{{ __('Try again') }}</button>
    <p class="address-selected-summary" data-address-summary hidden></p>
    <input type="hidden" id="{{ $addressId }}" name="{{ $addressName }}" value="">
    <input type="hidden" id="{{ $pointId }}-latitude" name="shipping_latitude" value="">
    <input type="hidden" id="{{ $pointId }}-longitude" name="shipping_longitude" value="">
    @error('address_id')<p class="field-error">{{ $message }}</p>@enderror
</div>
