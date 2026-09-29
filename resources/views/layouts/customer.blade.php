<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>{{ config('app.name', 'Warbun') }} - @yield('title', 'Shop')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                    <span class="text-white text-sm">🏪</span>
                </div>
                <span class="font-bold text-gray-800">Warbun</span>
            </a>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('customer.dashboard') }}" class="text-sm text-gray-600 hover:text-primary">My Account</a>
                @else
                    <a href="{{ route('customer.login') }}" class="text-sm text-primary font-medium">Login</a>
                @endauth
                <a href="{{ route('customer.cart') }}" class="relative p-2 hover:bg-gray-100 rounded-lg">
                    🛒
                    <span id="cartCount" class="absolute -top-1 -right-1 w-5 h-5 bg-primary text-white text-[10px] rounded-full flex items-center justify-center hidden">0</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="max-w-4xl mx-auto px-4 py-6">
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-4xl mx-auto px-4 py-6 text-center text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} Warbun. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
