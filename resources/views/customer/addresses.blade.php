@extends('layouts.customer')
@section('title', __('My addresses'))
@section('content')
<div class="catalog-heading address-page-heading"><div><h1>{{ __('My addresses') }}</h1><p>{{ __('Keep your home, office and other delivery destinations here.') }}</p></div><button type="button" class="button-primary" data-address-add>{{ __('Add address') }}</button></div>
<p data-address-status role="status" class="checkout-help"></p>
<button type="button" class="button-secondary" data-address-retry hidden>{{ __('Try again') }}</button>
<div id="addressCards" class="address-cards"></div>
<template id="addressCardTemplate">
    <article class="address-card">
        <div class="address-selector-heading"><h2 data-label></h2><span data-default class="address-default-label" hidden>{{ __('Main address') }}</span></div>
        <p data-recipient></p><p data-phone class="checkout-help"></p><p data-street class="address-street"></p>
        <a data-map class="delivery-point-link" target="_blank" rel="noopener noreferrer" hidden>{{ __('Open delivery point') }}</a>
        <div class="address-card-actions">
            <button type="button" class="button-secondary" data-set-default>{{ __('Use as main address') }}</button>
            <button type="button" class="crud-action crud-action-edit" data-edit aria-label="{{ __('Edit address') }}" title="{{ __('Edit address') }}"><x-icon name="pen-to-square" /></button>
            <button type="button" class="crud-action crud-action-delete" data-delete aria-label="{{ __('Delete address') }}" title="{{ __('Delete address') }}"><x-icon name="trash-can" /></button>
        </div>
    </article>
</template>
@include('components.address-book', ['mode' => 'manage', 'customer' => $customer])
@include('components.delivery-map-dialog')
@endsection
