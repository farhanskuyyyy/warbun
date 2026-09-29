@extends('layouts.app')
@section('title', 'Sales History')
@section('header', 'Sales History')

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <form method="GET" class="flex gap-2">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <span class="self-center text-gray-500">to</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Sale #</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Customer</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Cashier</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Total</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Payment</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $sale->sale_number }}</td>
                    <td class="px-4 py-3">{{ $sale->customer?->name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $sale->user->name }}</td>
                    <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">{{ ucfirst($sale->payment_method) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $sale->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $sale->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $sale->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('pos.receipt', $sale) }}" class="text-primary hover:text-primary-dark">Receipt</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No sales found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $sales->withQueryString()->links() }}</div>
</div>
@endsection
