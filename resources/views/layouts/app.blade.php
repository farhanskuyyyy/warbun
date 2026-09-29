<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>{{ config('app.name', 'Warbun') }} - @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Header -->
    <header class="md:hidden bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="flex items-center justify-between px-4 py-3">
            <button onclick="toggleSidebar()" class="p-2 hover:bg-gray-100 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-lg font-bold text-primary">🏪 Warbun</h1>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </header>

    <div class="flex min-h-screen">
        <!-- Sidebar Overlay (mobile) -->
        <div id="sidebarOverlay" class="hidden md:hidden fixed inset-0 bg-black/50 z-40" onclick="toggleSidebar()"></div>
        
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar fixed md:sticky top-0 left-0 w-64 h-full bg-white border-r border-gray-200 z-50 overflow-y-auto">
            <div class="p-4 border-b border-gray-200">
                <h1 class="text-xl font-bold text-primary">🏪 Warbun</h1>
                <p class="text-xs text-gray-500">Manajemen Warung</p>
            </div>
            <nav class="p-3 space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    📊 <span>Dashboard</span>
                </a>
                <div class="pt-3 pb-1 px-3"><p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Catalog</p></div>
                <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('products.*') ? 'bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    📦 <span>Products</span>
                </a>
                <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('categories.*') ? 'bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    🏷️ <span>Categories</span>
                </a>
                <div class="pt-3 pb-1 px-3"><p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Operations</p></div>
                <a href="{{ route('pos.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('pos.*') ? 'bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    💰 <span>POS / Cashier</span>
                </a>
                <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('inventory.*') ? 'bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    📦 <span>Inventory</span>
                </a>
                <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('orders.*') ? 'bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    🛒 <span>Orders</span>
                </a>
                <div class="pt-3 pb-1 px-3"><p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Finance</p></div>
                <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('customers.*') ? 'bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    👥 <span>Customers</span>
                </a>
                <a href="{{ route('debt.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('debt.*') ? 'bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    💳 <span>Debt</span>
                </a>
                <a href="{{ route('payments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('payments.*') ? 'bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    💵 <span>Payments</span>
                </a>
                <div class="pt-3 pb-1 px-3"><p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Admin</p></div>
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('reports.*') ? 'bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    📈 <span>Reports</span>
                </a>
                <a href="{{ route('audit.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('audit.*') ? 'bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    📋 <span>Audit Log</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content flex-1 md:ml-64">
            <!-- Desktop Header -->
            <header class="hidden md:block bg-white border-b border-gray-200 sticky top-0 z-20">
                <div class="flex items-center justify-between px-6 py-3">
                    <h2 class="text-lg font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 md:p-6">
                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm flex items-center gap-2">
                        <span>✅</span> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-center gap-2">
                        <span>❌</span> {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('hidden');
        }
    </script>
</body>
</html>
