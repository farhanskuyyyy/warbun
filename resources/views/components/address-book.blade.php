@props(['mode' => 'cart', 'customer' => null])
@php
    $customer = $customer ?? auth()->user()?->customer;
    $available = $mode === 'pos' || (bool) $customer;
@endphp
<dialog id="addressDialog" class="address-dialog" aria-labelledby="addressDialogTitle">
    <div class="address-selector-heading"><h2 id="addressDialogTitle">{{ __('Add address') }}</h2><button type="button" class="button-secondary" id="closeAddressDialog" aria-label="{{ __('Close') }}"><x-icon name="xmark" /></button></div>
    @if($available)
        <form id="addressForm">
            <div class="address-dialog-body">
                <p class="checkout-help">{{ __('Save this destination so you can choose it for your next order.') }}</p>
                <label class="field">{{ __('Address label') }}<input name="label" required maxlength="80" placeholder="{{ __('For example: Home or Office') }}"></label>
                <div class="address-recipient-fields">
                    <label class="field">{{ __('Recipient name') }}<input name="recipient_name" required maxlength="255" autocomplete="name"></label>
                    <label class="field">{{ __('Recipient phone') }}<input name="phone" required maxlength="30" type="tel" autocomplete="tel"></label>
                </div>
                <label class="field">{{ __('Full address') }}<textarea name="address" rows="3" required maxlength="2000" autocomplete="street-address"></textarea><span class="checkout-help">{{ __('Include the street, house number and a nearby landmark.') }}</span></label>
                <x-delivery-point id="addressBookPoint" latitude-name="latitude" longitude-name="longitude" />
                <label class="address-default-choice"><input name="is_default" type="checkbox"><span data-default-choice-label>{{ __('Use as main address') }}</span></label>
                <p id="addressFormError" class="field-error" role="alert"></p>
            </div>
            <div class="address-dialog-footer"><button id="cancelAddressDialog" type="button" class="button-secondary">{{ __('Cancel') }}</button><button id="saveAddressBtn" class="button-primary" type="submit">{{ __('Save address') }}</button></div>
        </form>
    @else
        <p class="checkout-help">{{ __('Sign in to save and choose your delivery addresses. Your cart will stay here.') }}</p>
        <a class="button-primary mt-5" href="{{ route('customer.checkout.continue') }}">{{ __('Sign in and continue') }}</a>
    @endif
</dialog>
<script id="address-book-config" type="application/json">{!! json_encode([
    'mode' => $mode, 'available' => $available, 'customerId' => $mode === 'pos' ? null : $customer?->id,
    'recipient' => $customer?->name, 'phone' => $customer?->phone,
    'addresses' => $mode === 'pos' ? [] : ($customer?->addresses ?? []),
    'url' => $mode === 'pos' ? url('/pos/customers/__CUSTOMER__/addresses') : route('customer.addresses.store'),
    'listUrl' => route('customer.addresses.index'), 'csrf' => csrf_token(),
    'text' => [
        'add' => __('Add address'), 'edit' => __('Edit address'), 'delete' => __('Delete address'), 'save' => __('Save address'), 'saving' => __('Saving address...'),
        'choose' => __('Choose an address'), 'empty' => __('No saved addresses yet. Add an address to arrange delivery.'),
        'customer' => __('Choose a customer first, then choose their delivery address.'),
        'loading' => __('Loading addresses...'), 'failed' => __('Unable to load addresses. Try again.'),
        'saveFailed' => __('Unable to save the address. Check the details and retry.'), 'deleteFailed' => __('Unable to remove the address. Try again.'),
        'confirmDelete' => __('Remove this saved address? Existing orders keep their delivery details.'),
        'main' => __('Main address'), 'makeMain' => __('Use as main address'), 'saved' => __('Address saved and selected.'), 'signin' => __('Sign in to choose a saved address.'),
    ],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
