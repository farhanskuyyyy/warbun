<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Warbun · @yield('title', __('Dashboard'))</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="cms-shell">
<a class="skip-link" href="#main">{{ __('Skip to content') }}</a>
<aside class="desktop-sidebar">@include('components.workspace-navigation')</aside>
<dialog id="workspace-menu" class="workspace-drawer"><div class="drawer-heading"><span>{{ __('Navigation') }}</span><button type="button" data-close-menu aria-label="{{ __('Close menu') }}"><x-icon name="xmark" /></button></div>@include('components.workspace-navigation')</dialog>
<div class="workspace-body">
<header class="workspace-header"><div class="workspace-context"><button class="mobile-menu-button" type="button" data-open-menu aria-controls="workspace-menu" aria-haspopup="dialog"><x-icon name="bars" /> <span>{{ __('Menu') }}</span></button><span class="context-label">{{ __('Workspace') }}</span><span class="context-divider">/</span><span>@yield('title', __('Dashboard'))</span></div><div class="header-tools"><a class="shop-shortcut" href="{{ route('customer.shop') }}"><x-icon name="store" /><span>{{ __('View shop') }}</span></a>@include('components.account-menu')</div></header>
<main id="main" class="cms-content"><div class="page-heading"><p class="eyebrow">Warbun / {{ __('Management') }}</p><h1>@if(isset($header)){{ $header }}@else @yield('header', __('Dashboard')) @endif</h1></div>@include('components.feedback')@if(isset($slot)){{ $slot }}@else @yield('content') @endif</main>
<footer class="workspace-footer"><span>Warbun · {{ __('Workspace') }}</span><a href="{{ route('customer.shop') }}">{{ __('Browse products') }} ↗</a></footer>
</div>@stack('scripts')
</body></html>
