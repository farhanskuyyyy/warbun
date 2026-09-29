<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Warbun') }} - {{ $title ?? 'Login' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-red-50 via-white to-orange-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <span class="text-3xl">🏪</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Warbun</h1>
            <p class="text-gray-500 text-sm">Sistem Manajemen Warung</p>
        </div>
        
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            {{ $slot }}
        </div>
        
        <!-- Footer -->
        <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} Warbun. All rights reserved.</p>
    </div>
</body>
</html>
