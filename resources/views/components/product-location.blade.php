@props(['product'])
<span class="product-location"><x-icon name="layer-group" />{{ $product?->location_label ?? __('Location not assigned') }}</span>
