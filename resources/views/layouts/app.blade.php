<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ __('Warbun') }} · @yield('title', __('Dashboard'))</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="bg-stone-50 text-gray-900">
<a class="sr-only focus:not-sr-only" href="#main">{{ __('Skip to content') }}</a>
<header class="flex flex-wrap items-center justify-between gap-3 border-b bg-white px-4 py-3">
<a href="{{ route('landing') }}" class="font-bold text-xl text-primary">{{ __('Warbun') }}</a>
<div class="flex flex-wrap items-center gap-3">
@include('components.locale-switcher')
@if(auth()->check())<a href="{{ route('profile.edit') }}">{{ auth()->user()->name }}</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">{{ __('Logout') }}</button></form>@else<a href="{{ route('login') }}">{{ __('Login') }}</a>@endif
</div>
</header>
<div class="md:flex min-h-screen">
<aside class="w-full md:w-60 md:shrink-0 border-r bg-white p-3">
<details id="navigation" class="md:block" open><summary class="md:hidden font-semibold">{{ __('Menu') }}</summary>
<nav aria-label="{{ __('Main navigation') }}" class="flex flex-col gap-1 mt-2">
@php($links = [
['dashboard','Dashboard','dashboard.view'],['products.index','Products','products.view'],['categories.index','Categories','products.view'],['product-types.index','Product Types','products.view'],['brands.index','Brands','products.view'],['units.index','Units','products.view'],['suppliers.index','Suppliers','products.view'],['pos.index','POS / Cashier','pos.access'],['pos.history','Sales History','sales.view'],['inventory.index','Inventory','inventory.view'],['opnames.index','Stock Opname','inventory.opname'],['orders.index','Orders','orders.view'],['customers.index','Customers','customers.view'],['debt.index','Debt','debt.view'],['payments.index','Payments','payments.view'],['refunds.index','Returns / Refunds','payments.view'],['shifts.index','Cashier Shifts','pos.access'],['users.index','Users & Roles','users.view'],['reports.index','Reports','reports.view'],['settings.index','Settings','settings.view'],['audit.index','Audit Log','audit.view']])
@foreach($links as [$route,$label,$permission]) @can($permission)<a href="{{ route($route) }}" class="rounded px-3 py-2 {{ request()->routeIs($route) ? 'bg-red-50 text-primary font-semibold' : 'hover:bg-stone-100' }}" @if(request()->routeIs($route)) aria-current="page" @endif>{{ __($label) }}</a>@endcan @endforeach
<a href="{{ route('customer.shop') }}" class="px-3 py-2">{{ __('Shop') }}</a>
@if(auth()->user()?->customer)<a href="{{ route('customer.history') }}" class="px-3 py-2">{{ __('My orders') }}</a>@endif
</nav></details></aside>
<main id="main" class="min-w-0 flex-1 p-4 md:p-6">
<h1 class="text-2xl font-semibold mb-5">@if(isset($header)){{ $header }}@else @yield('header', __('Dashboard')) @endif</h1>
@include('components.feedback')
@if(isset($slot)){{ $slot }}@else @yield('content') @endif
</main></div>@stack('scripts')
</body></html>
