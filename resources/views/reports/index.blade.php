@extends('layouts.app')
@section('title', 'Reports')
@section('header', 'Reports')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <a href="{{ route('reports.sales') }}" class="bg-white rounded-xl p-6 border border-gray-100 hover:border-primary transition">
        <div class="text-3xl mb-2">📊</div>
        <h3 class="font-semibold text-lg">Sales Report</h3>
        <p class="text-sm text-gray-500">Daily sales, transactions, revenue</p>
    </a>
    <a href="{{ route('reports.inventory') }}" class="bg-white rounded-xl p-6 border border-gray-100 hover:border-primary transition">
        <div class="text-3xl mb-2">📦</div>
        <h3 class="font-semibold text-lg">Inventory Report</h3>
        <p class="text-sm text-gray-500">Stock levels, low stock alerts</p>
    </a>
    <a href="{{ route('reports.debt') }}" class="bg-white rounded-xl p-6 border border-gray-100 hover:border-primary transition">
        <div class="text-3xl mb-2">💳</div>
        <h3 class="font-semibold text-lg">Debt Report</h3>
        <p class="text-sm text-gray-500">Outstanding, overdue, payments</p>
    </a>
</div>
@endsection
