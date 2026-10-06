@props(['id', 'latitude' => null, 'longitude' => null, 'latitudeName' => 'shipping_latitude', 'longitudeName' => 'shipping_longitude'])
<div class="delivery-point" data-delivery-point="{{ $id }}">
    <input type="hidden" id="{{ $id }}-latitude" name="{{ $latitudeName }}" data-point-latitude value="{{ old($latitudeName, $latitude) }}">
    <input type="hidden" id="{{ $id }}-longitude" name="{{ $longitudeName }}" data-point-longitude value="{{ old($longitudeName, $longitude) }}">
    <p>{{ __('Delivery point (optional)') }}</p>
    <div class="delivery-point-actions"><button type="button" class="button-secondary" data-location-open="{{ $id }}">{{ __('Choose on map') }}</button><button type="button" class="button-secondary" data-location-clear hidden>{{ __('Remove point') }}</button></div>
    <p class="checkout-help" data-point-status role="status">{{ __('No map point selected. Your written address can still be used.') }}</p>
    @error($latitudeName)<p class="field-error">{{ $message }}</p>@enderror
    @error($longitudeName)<p class="field-error">{{ $message }}</p>@enderror
</div>
