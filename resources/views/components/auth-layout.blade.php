@props(['title' => __('Login')])
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Warbun · {{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-shell">
    <a class="skip-link" href="#main">{{ __('Skip to content') }}</a>
    <header class="auth-header">
        <a class="brand" href="{{ route('landing') }}">warbun<span>{{ __('Your everyday essentials') }}</span></a>
        <div class="auth-header-tools"><a href="{{ route('customer.shop') }}">{{ __('Browse products') }}</a><div class="auth-language">@include('components.locale-switcher')</div></div>
    </header>
    <main id="main" class="auth-main">
        <section class="auth-story" aria-label="{{ __('Warbun') }}">
            <p class="eyebrow">{{ __('Your everyday essentials') }}</p>
            <h2>{{ __('Everyday shopping, a little easier.') }}</h2>
            <p class="auth-story-description">{{ __('A familiar shop, now within reach. Choose your essentials and collect them at the store or have them delivered.') }}</p>
            <div class="auth-store-note"><span class="status-dot" aria-hidden="true"></span>{{ __('Pickup or delivery. Your choice.') }}</div>
            <a class="auth-story-link" href="{{ route('customer.shop') }}">{{ __('Browse products') }} <span aria-hidden="true">↗</span></a>
        </section>
        <section class="auth-form-section" aria-label="{{ $title }}">
            <div class="auth-form-container">
                @include('components.feedback')
                @if(session('status'))<p class="auth-session-status" role="status">{{ session('status') === 'verification-link-sent' ? __('A new verification link has been sent to the email address you provided during registration.') : __(session('status')) }}</p>@endif
                {{ $slot }}
            </div>
        </section>
    </main>
    <footer class="auth-footer"><span>© {{ date('Y') }} Warbun</span><span>{{ __('Pickup or delivery. Your choice.') }}</span></footer>
</body></html>
