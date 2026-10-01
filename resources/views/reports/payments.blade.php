@extends('layouts.app')
@section('title', __('Payment report'))
@section('header', __('Payment report'))
@section('content')<div class="panel mb-4">@foreach($totals as $t)<p>{{ __('status.'.$t->method) }}: {{ \App\Support\Money::format($t->amount) }}</p>@endforeach</div><div class="panel overflow-x-auto"><table class="w-full"><thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Staff') }}</th><th>{{ __('Amount') }}</th></tr></thead><tbody>@forelse($payments as $p)<tr><td>{{ $p->payment_number }}</td><td>{{ $p->user->name }}</td><td>{{ \App\Support\Money::format($p->amount) }}</td></tr>@empty<tr><td colspan="3">{{ __('No transactions yet') }}</td></tr>@endforelse</tbody></table>{{ $payments->links() }}</div>@endsection
