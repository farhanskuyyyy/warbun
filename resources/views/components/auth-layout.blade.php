<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Warbun') }} - {{ $title ?? 'Login' }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])

</head>
<body class="min-h-screen bg-stone-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="{{ route('landing') }}" class="brand mb-4">warbun</a>
            <p class="text-gray-500 text-sm">{{ __('Sistem Manajemen Warung') }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-stone-200 p-8">
            @include('components.locale-switcher')
            @include('components.feedback')
            {{ $slot }}
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} Warbun · <a class="inline-flex items-center min-h-[44px]" href="{{ route('customer.shop') }}">{{ __('Browse products') }}</a></p>
    </div>
</body>
</html>
