@extends('layouts.app')
@section('title', __('Order monitoring'))
@section('header', __('Order monitoring'))
@section('content')
<div class="monitor-heading"><div><h2>{{ __('What needs processing?') }}</h2><p>{{ __('Online orders and cashier deliveries, oldest active orders first.') }}</p></div><a class="button-secondary" href="{{ route('orders.monitor', request()->only('status','search')) }}">{{ __('Refresh queue') }}</a></div>
<nav class="monitor-stages" aria-label="{{ __('Filter order status') }}">
    <a href="{{ route('orders.monitor', ['search'=>$search]) }}" @if($status === 'active') aria-current="page" @endif><span>{{ __('Active queue') }}</span><strong>{{ array_sum($counts) }}</strong></a>
    @foreach($counts as $stage => $count)<a href="{{ route('orders.monitor', ['status'=>$stage,'search'=>$search]) }}" @if($status === $stage) aria-current="page" @endif><span>{{ __('status.'.$stage) }}</span><strong>{{ $count }}</strong></a>@endforeach
</nav>
<form method="GET" class="monitor-filters panel"><label class="field">{{ __('Find customer or reference') }}<input name="search" type="search" value="{{ $search }}" maxlength="100"></label><label class="field">{{ __('Status') }}<select name="status">@foreach(['active','pending','confirmed','preparing','ready','completed','cancelled','refunded'] as $stage)<option value="{{ $stage }}" @selected($status === $stage)>{{ $stage === 'active' ? __('Active queue') : __('status.'.$stage) }}</option>@endforeach</select></label><button class="button-primary">{{ __('Filter orders') }}</button></form>
<div class="monitor-orders">
@forelse($entries as $entry)
@php
    $isOnline = $entry->source === 'online';
    $record = $isOnline ? $orders[$entry->id] : $sales[$entry->id];
    $stage = $entry->stage;
    $next = ['pending'=>'confirmed','confirmed'=>'preparing','preparing'=>'ready','ready'=>'completed'][$stage] ?? null;
    $permission = $next === 'confirmed' ? 'orders.confirm' : ($next === 'completed' ? 'orders.complete' : 'orders.update');
    $canAdvance = $next && auth()->user()->can('orders.update') && auth()->user()->can($permission) && (!$isOnline || $next !== 'completed' || $record->payment_status === 'paid');
    $actions = ['confirmed'=>'Confirm order','preparing'=>'Start preparing','ready'=>'Mark ready','completed'=>'Mark completed'];
@endphp
<article class="panel monitor-order" data-monitor-source="{{ $entry->source }}" data-monitor-id="{{ $record->id }}">
    <header><span class="monitor-source">{{ $isOnline ? __('Online order') : __('Cashier delivery') }}</span><span class="badge">{{ __('status.'.$stage) }}</span></header>
    <h3>{{ $record->customer?->name ?? __('Customer unavailable') }}</h3>
    <p class="monitor-reference">{{ $isOnline ? $record->order_number : $record->sale_number }}<br>{{ $record->created_at->translatedFormat('d M Y · H:i') }}</p>
    <div class="monitor-destination"><x-icon :name="$record->fulfillment_type === 'delivery' ? 'truck' : 'bag-shopping'" /><div><strong>{{ $record->fulfillment_type === 'delivery' ? __('Delivery') : __('Pickup') }}</strong><p>{{ $record->shipping_address ?: __('Collect at the store') }}</p>@if($record->customer?->phone)<p>{{ $record->customer->phone }}</p>@endif</div></div>
    <details><summary>{{ __('Items') }} · {{ $record->items->sum('quantity') }}</summary><ul>@foreach($record->items as $item)<li>{{ $item->quantity }} × {{ $item->product?->name ?? __('Unavailable product') }}</li>@endforeach</ul>@if($record->notes)<p>{{ $record->notes }}</p>@endif</details>
    <div class="monitor-payment"><strong>{{ \App\Support\Money::format($record->total) }}</strong><span>@if($isOnline){{ __('status.'.$record->payment_status) }}@elseif($record->debt_amount > 0){{ __('Debt remaining') }}: {{ \App\Support\Money::format($record->debt_amount) }}@else{{ __('status.paid') }}@endif</span></div>
    @if($isOnline && $stage === 'ready' && $record->payment_status !== 'paid')<p class="monitor-notice">{{ __('Record payment before completing this order.') }}</p>@endif
    <footer>
        @if($canAdvance)<form method="POST" action="{{ $isOnline ? route('orders.update-status',$record) : route('orders.delivery-status',$record) }}">@csrf @method('PUT')<input type="hidden" name="status" value="{{ $next }}"><button class="button-primary">{{ __($actions[$next]) }}</button></form>@endif
        @if($isOnline)<a class="button-secondary" href="{{ route('orders.show',$record) }}">{{ __('Order details') }}</a>@else @can('sales.view')@if($record->user_id === auth()->id() || auth()->user()->can('reports.staff'))<a class="button-secondary" href="{{ route('pos.receipt',$record) }}">{{ __('Receipt') }}</a>@endif @endcan @endif
    </footer>
</article>
@empty
<div class="panel monitor-empty"><h2>{{ __('No orders in this queue') }}</h2><p>{{ __('Choose another status or clear your search.') }}</p><a class="button-secondary" href="{{ route('orders.monitor') }}">{{ __('Show active orders') }}</a></div>
@endforelse
</div>
<div class="mt-6">{{ $entries->links() }}</div>
@endsection
