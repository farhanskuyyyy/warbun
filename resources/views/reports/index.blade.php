@extends('layouts.app')
@section('title', __('Reports'))
@section('header', __('Reports'))

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <a href="{{ route('reports.sales') }}" class="bg-white rounded-xl p-6 border border-gray-100 hover:border-primary transition">
        <div class="text-3xl mb-2">📊</div>
        <h3 class="font-semibold text-lg">{{ __('Sales Report') }}</h3>
        <p class="text-sm text-gray-500">{{ __('Daily sales, transactions, revenue') }}</p>
    </a>
    <a href="{{ route('reports.inventory') }}" class="bg-white rounded-xl p-6 border border-gray-100 hover:border-primary transition">
        <div class="text-3xl mb-2">📦</div>
        <h3 class="font-semibold text-lg">{{ __('Inventory Report') }}</h3>
        <p class="text-sm text-gray-500">{{ __('Stock levels, low stock alerts') }}</p>
    </a>
    <a href="{{ route('reports.debt') }}" class="bg-white rounded-xl p-6 border border-gray-100 hover:border-primary transition">
        <div class="text-3xl mb-2">💳</div>
        <h3 class="font-semibold text-lg">{{ __('Debt Report') }}</h3>
        <p class="text-sm text-gray-500">{{ __('Outstanding, overdue, payments') }}</p>
    </a>
</div>
@can('reports.payments')<a href="{{ route('reports.payments') }}" class="panel block mt-4">{{ __('Payment report') }}</a>@endcan
@can('reports.staff')<a href="{{ route('reports.staff') }}" class="panel block mt-4">{{ __('Staff report') }}</a>@endcan
@endsection
