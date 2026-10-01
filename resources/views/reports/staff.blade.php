@extends('layouts.app')
@section('title', __('Staff report'))
@section('header', __('Staff report'))
@section('content')
<div class="panel mb-4 overflow-x-auto"><table class="w-full"><thead><tr><th>{{ __('Staff') }}</th><th>{{ __('Transactions') }}</th><th>{{ __('Gross transaction totals') }}</th><th>{{ __('Receipts collected') }}</th><th>{{ __('Debt created') }}</th><th>{{ __('Refunds issued') }}</th></tr></thead><tbody>@foreach($staff as $u)<tr><td>{{ $u->name }}</td><td>{{ $u->sales_count }}</td>@foreach(['sales_total','payments_total','debt_total','refund_total'] as $metric)<td>{{ \App\Support\Money::format($u->$metric) }}</td>@endforeach</tr>@endforeach</tbody></table></div>
@include('operations.shift-table')
@endsection
