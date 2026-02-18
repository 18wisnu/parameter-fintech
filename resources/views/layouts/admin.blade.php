<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Parameter Fintech</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> body { font-family: 'Outfit', sans-serif; } [x-cloak] { display: none !important; } </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

    <header class="fixed top-0 left-0 right-0 h-16 bg-white shadow-sm z-40 flex items-center justify-between px-4 md:hidden">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <span class="font-bold text-lg text-sky-600 tracking-wider">PARAMETER</span>
        </div>
        
        <div class="relative mr-4" x-data="{ open: false }">
    <button @click="open = !open" class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute top-1 right-1 w-4 h-4 bg-rose-500 rounded-full text-[10px] text-white flex items-center justify-center font-bold border border-white">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
        @endif
    </button>

    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl overflow-hidden z-50 border border-slate-100" style="display: none;">
        <div class="p-3 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-xs font-bold text-slate-700 uppercase">Notifikasi</h3>
            <a href="{{ route('notifications.readAll') }}" class="text-[10px] text-sky-600 hover:text-sky-800 font-bold">Tandai Sudah Baca</a>
        </div>
        
        <div class="max-h-64 overflow-y-auto">
            @forelse(auth()->user()->notifications as $notification)
                <a href="{{ $notification->data['link'] ?? '#' }}" class="block p-4 border-b border-slate-50 hover:bg-slate-50 transition {{ $notification->read_at ? 'opacity-60' : 'bg-sky-50/50' }}">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $notification->data['color'] ?? 'bg-slate-400' }} flex items-center justify-center text-white">
                            @if(($notification->data['icon'] ?? '') == 'money')
                                <span>💰</span>
                            @else
                                <span>👤</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">{{ $notification->data['title'] }}</p>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $notification->data['message'] }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    </a>
                @empty
                    <div class="p-4 text-center text-slate-400 text-xs">Tidak ada notifikasi baru.</div>
                @endforelse
            </div>
        </div>
</div>

        <div class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs shadow-md">
                    {{ substr(Auth::user()->name, 0, 1) }}
        </div>
    </header>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-black/50 z-50 md:hidden" x-cloak></div>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed top-0 left-0 z-50 w-64 h-full bg-sky-600 text-white transition-transform duration-300 ease-in-out md:translate-x-0 shadow-2xl md:shadow-none flex flex-col">
        
        <div class="h-20 hidden md:flex items-center justify-center border-b border-sky-500">
            <h1 class="text-2xl font-bold tracking-wider text-white">PARAMETER</h1>
        </div>

        <div class="h-16 flex md:hidden items-center justify-between px-6 border-b border-sky-500 bg-sky-700">
            <span class="font-bold tracking-wider text-white">MENU</span>
            <button @click="sidebarOpen = false" class="text-sky-200 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-2">
            
            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-white text-sky-700 shadow-md font-bold' : 'text-sky-100 hover:bg-sky-500 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Dashboard
            </a>

            <a href="{{ route('customers.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('customers.*') ? 'bg-white text-sky-700 shadow-md font-bold' : 'text-sky-100 hover:bg-sky-500 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Data Pelanggan
            </a>

            <a href="{{ route('invoices.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('invoices.*') ? 'bg-white text-sky-700 shadow-md font-bold' : 'text-sky-100 hover:bg-sky-500 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Tagihan / Invoice
            </a>

            <a href="{{ route('reports.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('reports.*') ? 'bg-white text-sky-700 shadow-md font-bold' : 'text-sky-100 hover:bg-sky-500 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Bagi Hasil
            </a>

            <p class="px-4 text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 mt-4">Laporan</p>

            <a href="{{ route('reports.reserve') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('reports.reserve') ? 'bg-white text-sky-700 font-bold' : 'text-sky-100 hover:bg-sky-500' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Dana Cadangan
            </a>

            <a href="{{ route('reports.history') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('reports.history') ? 'bg-white text-sky-700 font-bold' : 'text-sky-100 hover:bg-sky-500' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Riwayat Transaksi
            </a>

        </nav>

        <div class="p-4 bg-sky-700 border-t border-sky-600">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="flex items-center text-sky-100 hover:text-white w-full text-sm font-bold transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Keluar Aplikasi
                </button>
            </form>
        </div>
    </aside>

    <main class="min-h-screen bg-slate-50 transition-all pt-16 md:pt-0 md:ml-64">
        <div class="p-4 md:p-8 max-w-7xl mx-auto">
            @yield('content')
        </div>
    </main>

</body>
</html>