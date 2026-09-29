@extends('layouts.app')
@section('title', 'Sales Report')
@section('header', 'Sales Report')

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <form method="GET" class="flex gap-2">
            <input type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <span class="self-center text-gray-500">to</span>
            <input type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium">Filter</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Total Sales</p>
            <p class="text-2xl font-bold text-primary">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Transactions</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalTransactions }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-100">
            <p class="text-xs text-gray-500 uppercase">Avg Transaction</p>
            <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($avgTransaction, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold">Daily Sales</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-right px-4 py-3">Transactions</th>
                    <th class="text-right px-4 py-3">Total Sales</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dailySales as $day)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">{{ Carbon::parse($day->date)->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-right">{{ $day->count }}</td>
                    <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($day->total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
