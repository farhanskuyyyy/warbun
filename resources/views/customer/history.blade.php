@extends('layouts.customer')
@section('title', __('My orders'))
@section('content')
<div class="catalog-heading"><p class="eyebrow">{{ __('Your Warbun account') }}</p><h1>{{ __('My orders') }}</h1><p>{{ __('Follow the status of this order from your account.') }}</p></div>
<div class="order-history-grid">
@forelse($orders as $order)
    <article class="order-history-card">
        <p class="checkout-help">{{ $order->created_at->translatedFormat('d M Y · H:i') }}</p><h2 class="order-reference">{{ $order->order_number }}</h2>
        <div class="order-status-row"><span>{{ __('Status') }}: <strong>{{ __('status.'.$order->status) }}</strong></span><span>{{ __('Payment') }}: <strong>{{ __('status.'.$order->payment_status) }}</strong></span></div>
        <div class="order-card-footer"><strong>{{ \App\Support\Money::format($order->total) }}</strong><a class="button-primary" href="{{ route('customer.order.success', $order) }}">{{ __('View order') }}</a></div>
    </article>
@empty
    <div class="cart-empty"><h2>{{ __('No transactions yet') }}</h2><p>{{ __('Choose your everyday essentials from the catalog.') }}</p><a class="button-primary" href="{{ route('customer.shop') }}">{{ __('Browse products') }}</a></div>
@endforelse
</div><div class="catalog-pagination">{{ $orders->links() }}</div>
@if($customer->can_use_debt)<h2 class="text-xl font-semibold mt-6 mb-3">{{ __('Debt ledger') }}</h2><p>{{ __('Outstanding') }}: {{ \App\Support\Money::format($customer->outstanding_balance) }}</p><div class="panel overflow-x-auto"><table><thead><tr><th>{{ __('Date') }}</th><th>{{ __('Type') }}</th><th>{{ __('Debit') }}</th><th>{{ __('Credit') }}</th><th>{{ __('Balance') }}</th></tr></thead><tbody>@foreach($transactions as $entry)<tr><td>{{ $entry->created_at->translatedFormat('d M Y') }}</td><td>{{ __('status.'.$entry->type) }}</td><td>{{ \App\Support\Money::format($entry->debit_amount) }}</td><td>{{ \App\Support\Money::format($entry->credit_amount) }}</td><td>{{ \App\Support\Money::format($entry->balance_after) }}</td></tr>@endforeach</tbody></table>{{ $transactions->links() }}</div>@endif
<h2 class="text-xl font-semibold mt-6 mb-3">{{ __('In-store purchases') }}</h2><div class="panel overflow-x-auto"><table class="w-full"><thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Date') }}</th><th>{{ __('Total') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>@forelse($sales as $sale)<tr><td>{{ $sale->sale_number }}</td><td>{{ $sale->created_at->translatedFormat('d M Y') }}</td><td>{{ \App\Support\Money::format($sale->total) }}</td><td>{{ __('status.'.$sale->status) }}</td></tr>@empty<tr><td colspan="4">{{ __('No transactions yet') }}</td></tr>@endforelse</tbody></table>{{ $sales->links() }}</div>
@endsection
