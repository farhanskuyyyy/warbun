@extends('layouts.app')
@section('title', __('Dashboard'))
@section('header', __('Business overview'))
@section('content')
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">@foreach($stats as $key=>$value)<div class="panel"><h2 class="text-sm text-gray-600">{{ __('metrics.'.$key) }}</h2><p class="text-2xl font-semibold mt-2">{{ in_array($key,['transactions_today','orders_today','active_shifts','pending_orders','failed_payments']) ? $value : \App\Support\Money::format($value) }}</p></div>@endforeach</div>
<div class="panel mb-5"><h2 class="font-semibold">{{ __('Stock alerts') }}</h2><p>{{ __('Low stock') }}: {{ $inventory_alerts['low_stock'] }} · {{ __('Out of stock') }}: {{ $inventory_alerts['out_of_stock'] }}</p></div>
<div class="panel"><h2 class="font-semibold mb-3">{{ __('Recent sales') }}</h2><div class="overflow-x-auto"><table class="w-full"><thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Staff') }}</th><th>{{ __('Total') }}</th></tr></thead><tbody>@forelse($recent_sales as $sale)<tr><td>@can('sales.view')<a href="{{ route('pos.receipt',$sale) }}">{{ $sale->sale_number }}</a>@else{{ $sale->sale_number }}@endcan</td><td>{{ $sale->user->name }}</td><td>{{ \App\Support\Money::format($sale->total) }}</td></tr>@empty<tr><td colspan="3">{{ __('No transactions yet') }}</td></tr>@endforelse</tbody></table></div></div>
@endsection
