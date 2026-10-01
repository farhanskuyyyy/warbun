<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Warbun') }} - {{ $title ?? 'Login' }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])

    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-stone-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <span class="text-3xl">🏪</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Warbun') }}</h1>
            <p class="text-gray-500 text-sm">{{ __('Sistem Manajemen Warung') }}</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl border border-stone-200 p-8">
            @include('components.locale-switcher')
            @include('components.feedback')
            {{ $slot }}
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} Warbun. All rights reserved.</p>
    </div>
</body>
</html>
