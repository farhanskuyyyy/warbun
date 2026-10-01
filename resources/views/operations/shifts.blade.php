@extends('layouts.app')
@section('title', __('Cashier Shifts'))
@section('header', __('Cashier Shifts'))
@section('content')
<div class="panel overflow-x-auto"><table class="w-full"><thead><tr><th>{{ __('Staff') }}</th><th>{{ __('Opened') }}</th><th>{{ __('Status') }}</th><th>{{ __('Expected cash') }}</th><th>{{ __('Actual cash') }}</th><th>{{ __('Variance') }}</th></tr></thead><tbody>@forelse($shifts as $s)<tr><td>{{ $s->user->name }}</td><td>{{ $s->opened_at->translatedFormat('d M Y H:i') }}</td><td>{{ __('status.'.$s->status) }}</td><td>{{ $s->expected_cash!==null?\App\Support\Money::format($s->expected_cash):'-' }}</td><td>{{ $s->closing_cash!==null?\App\Support\Money::format($s->closing_cash):'-' }}</td><td>{{ $s->cash_variance??'-' }}</td></tr>@empty<tr><td colspan="6">{{ __('No transactions yet') }}</td></tr>@endforelse</tbody></table>{{ $shifts->links() }}</div>
@endsection
