<dialog id="deliveryMapDialog" class="delivery-map-dialog" aria-labelledby="deliveryMapTitle" aria-describedby="deliveryMapHelp">
    <div class="pos-customer-heading"><h2 id="deliveryMapTitle">{{ __('Choose delivery point') }}</h2><button id="closeDeliveryMap" class="button-secondary" type="button" aria-label="{{ __('Close map') }}"><x-icon name="xmark" /></button></div>
    <div class="delivery-map-body">
        <p id="deliveryMapHelp">{{ __('Tap the destination or drag the pin. Keep the street, house number and landmark in the address field.') }}</p>
        <div class="delivery-map-tools"><button id="locateDeliveryMap" class="button-secondary" type="button" disabled>{{ __('Use my location') }}</button><button id="centerDeliveryMap" class="button-secondary" type="button" disabled>{{ __('Select map center') }}</button></div>
        <div id="deliveryMapCanvas" tabindex="0" role="region" aria-label="{{ __('Delivery location map') }}"></div>
        <p id="deliveryMapStatus" role="status" aria-live="polite"></p>
        <p id="deliveryMapError" class="field-error" role="alert"></p>
        <button id="retryDeliveryMap" type="button" class="button-secondary" hidden>{{ __('Retry map') }}</button>
    </div>
    <div class="delivery-map-footer"><button id="cancelDeliveryMap" type="button" class="button-secondary">{{ __('Cancel') }}</button><button id="confirmDeliveryMap" type="button" class="button-primary" disabled>{{ __('Use this point') }}</button><p class="delivery-map-attribution">{!! config('maps.attribution') !!}</p></div>
</dialog>
<script id="delivery-map-config" type="application/json">{!! json_encode([
    'tiles' => config('maps.tile_url'), 'attribution' => config('maps.attribution'),
    'center' => [config('maps.latitude'), config('maps.longitude')], 'zoom' => config('maps.zoom'),
    'text' => [
        'loading' => __('Loading map...'), 'choose' => __('Select a point on the map, or use your location.'),
        'selected' => __('Delivery point selected.'), 'empty' => __('No map point selected. Your written address can still be used.'),
        'failed' => __('The map could not load. Retry or use the written address.'),
        'tilesFailed' => __('Some map tiles could not load. Retry, or keep your written address without a map point.'),
        'locating' => __('Finding your location...'), 'located' => __('Location found. Move the pin if needed, then confirm.'),
        'denied' => __('Location access was denied. Select a point on the map instead.'),
        'unavailable' => __('Your location is unavailable. Select a point on the map instead.'),
        'timeout' => __('Location lookup timed out. Try again or select a point manually.'),
        'unsupported' => __('Location access is unavailable on this browser or connection. Select a point manually.'),
        'pin' => __('Delivery point'),
    ],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
