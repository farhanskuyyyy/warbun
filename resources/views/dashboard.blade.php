@extends('layouts.app')
@section('title', __('Dashboard'))
@section('header', __('Business overview'))
@section('content')
<section class="sales-summary" aria-label="{{ __('Today at a glance') }}"><div class="sales-lead"><h2>{{ __('metrics.sales_today') }}</h2><strong>{{ \App\Support\Money::format($stats['sales_today']) }}</strong></div><div class="sales-counts">@foreach(['transactions_today','orders_today'] as $key)<div><h2>{{ __('metrics.'.$key) }}</h2><p>{{ $stats[$key] }}</p></div>@endforeach</div></section>
<div class="metrics-grid">@foreach($stats as $key=>$value)@continue(in_array($key,['sales_today','transactions_today','orders_today']))<div class="panel"><h2>{{ __('metrics.'.$key) }}</h2><p>{{ in_array($key,['active_shifts','pending_orders','failed_payments']) ? $value : \App\Support\Money::format($value) }}</p></div>@endforeach</div>
<section class="stock-summary"><h2>{{ __('Stock alerts') }}</h2><p>{{ __('Low stock') }}: <strong>{{ $inventory_alerts['low_stock'] }}</strong> · {{ __('Out of stock') }}: <strong>{{ $inventory_alerts['out_of_stock'] }}</strong></p></section>
<div class="panel"><h2 class="font-semibold mb-3">{{ __('Recent sales') }}</h2><div class="overflow-x-auto"><table class="w-full"><thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Staff') }}</th><th>{{ __('Total') }}</th></tr></thead><tbody>@forelse($recent_sales as $sale)<tr><td>@can('sales.view')<a href="{{ route('pos.receipt',$sale) }}">{{ $sale->sale_number }}</a>@else{{ $sale->sale_number }}@endcan</td><td>{{ $sale->user->name }}</td><td>{{ \App\Support\Money::format($sale->total) }}</td></tr>@empty<tr><td colspan="3">{{ __('No transactions yet') }}</td></tr>@endforelse</tbody></table></div></div>
@endsection
