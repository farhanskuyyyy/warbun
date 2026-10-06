@props(['latitude', 'longitude'])
@if($url = \App\Support\DeliveryPoint::url($latitude, $longitude))
    <a class="delivery-point-link no-print" href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ __('Open delivery point') }}</a>
@endif
