@extends('layouts.customer')
@section('title', __('Shop'))
@section('content')
<div class="py-8 max-w-2xl"><h1 class="text-3xl font-semibold mb-3">{{ __('Welcome to Warbun') }}</h1><p class="text-gray-700 mb-6">{{ __('Browse the catalog, order for pickup or delivery, and track your purchases.') }}</p><a href="{{ route('customer.shop') }}" class="button-primary">{{ __('Browse products') }}</a></div>
@endsection
