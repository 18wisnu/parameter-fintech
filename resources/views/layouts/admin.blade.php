<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin {{ $appSettings['business_name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Outfit', sans-serif; } 
        [x-cloak] { display: none !important; } 
        .bg-theme { background-color: {{ $appSettings['theme_color'] }}; }
        .text-theme { color: {{ $appSettings['theme_color'] }}; }
        .border-theme { border-color: {{ $appSettings['theme_color'] }}; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

    <header class="fixed top-0 left-0 right-0 h-16 bg-white shadow-sm z-40 flex items-center justify-between px-4 md:hidden">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <div class="flex flex-col">
                <span class="font-extrabold text-xl text-theme tracking-tight leading-none">{{ $appSettings['business_name'] }}</span>
            </div>
        </div>
    </header>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 md:hidden" x-cloak></div>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed top-0 left-0 z-50 w-64 h-full bg-theme text-white transition-transform duration-300 ease-in-out md:translate-x-0 shadow-2xl md:shadow-none flex flex-col" style="background-color: {{ $appSettings['theme_color'] }}">
        
        <div class="h-24 hidden md:flex items-center justify-center border-b border-white/10 bg-black/10">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-6 hover:scale-105 transition-transform duration-300">
                <div class="bg-gradient-to-br from-white/20 to-white/5 p-2 rounded-xl backdrop-blur-sm border border-white/10 shadow-sm">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-black text-white tracking-tight leading-none">{{ $appSettings['business_name'] }}</span>
                    <span class="text-[10px] font-bold text-white/60 tracking-[0.25em] uppercase mt-1">Management</span>
                </div>
            </a>
        </div>


        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 custom-scrollbar">
            
            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-white text-slate-800 shadow-md font-bold' : 'text-white/80 hover:bg-black/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Dashboard
            </a>

            @if($appSettings['features']['customer_data'] ?? true)
            <a href="{{ route('customers.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('customers.*') ? 'bg-white text-slate-800 shadow-md font-bold' : 'text-white/80 hover:bg-black/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Data Pelanggan
            </a>
            @endif

            @if($appSettings['features']['invoices'] ?? true)
            <a href="{{ route('invoices.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('invoices.*') ? 'bg-white text-slate-800 shadow-md font-bold' : 'text-white/80 hover:bg-black/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Tagihan / Invoice
            </a>
            @endif

            @if($appSettings['features']['profit_sharing'] ?? true)
            <a href="{{ route('reports.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('reports.index') ? 'bg-white text-slate-800 shadow-md font-bold' : 'text-white/80 hover:bg-black/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Bagi Hasil
            </a>
            @endif

            @if($userRole == 'owner' || $userRole == 'admin')
            <a href="{{ route('admin.profit_sharing.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.profit_sharing.*') ? 'bg-white text-slate-800 shadow-md font-bold' : 'text-white/80 hover:bg-black/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Master Setting
            </a>
            @endif

            @if($appSettings['features']['salary_management'] ?? true)
            <a href="{{ route('admin.salaries.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.salaries.*') ? 'bg-white text-slate-800 shadow-md font-bold' : 'text-white/80 hover:bg-black/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Manajemen Gaji
            </a>
            @endif

            <div class="mt-6 mb-2">
                <p class="px-4 text-[10px] font-black text-white/50 uppercase tracking-widest">Laporan & Keuangan</p>
            </div>

            @if($appSettings['features']['reserve_fund'] ?? true)
            <a href="{{ route('reports.reserve') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('reports.reserve') ? 'bg-white text-slate-800 shadow-md font-bold' : 'text-white/80 hover:bg-black/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Dana Cadangan
            </a>
            @endif

            @if($appSettings['features']['transactions'] ?? true)
            <a href="{{ route('reports.history') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('reports.history') ? 'bg-white text-slate-800 shadow-md font-bold' : 'text-white/80 hover:bg-black/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Riwayat Transaksi
            </a>
            @endif

            @if($appSettings['features']['voucher_analysis'] ?? true)
            <a href="{{ route('reports.vouchers') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('reports.vouchers') ? 'bg-white text-slate-800 shadow-md font-bold' : 'text-white/80 hover:bg-black/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                Analisa Voucher
            </a>
            @endif

            <div class="mt-6 mb-2">
                <p class="px-4 text-[10px] font-black text-white/50 uppercase tracking-widest">Sistem</p>
            </div>

            <a href="{{ route('mobile.home') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('mobile.*') ? 'bg-white text-slate-800 shadow-md font-bold' : 'text-white/80 hover:bg-black/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Menu Setoran
            </a>

            @if($appSettings['features']['staff_data'] ?? true)
            <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.users.*') ? 'bg-white text-slate-800 shadow-md font-bold' : 'text-white/80 hover:bg-black/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Data Pegawai
            </a>
            @endif

            @if($userRole == 'owner')
                <a href="{{ route('admin.logs.index') }}" class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.logs.*') ? 'bg-white text-sky-800 shadow-md font-bold' : 'text-sky-100 hover:bg-sky-600/50 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Log Sistem
                </a>

                <a href="{{ route('admin.system.index') }}" class="flex items-center px-4 py-3 rounded-xl mt-4 bg-sky-800/50 border border-sky-400/20 transition-all {{ request()->routeIs('admin.system.*') ? 'bg-white text-sky-800 shadow-md font-bold border-none' : 'text-sky-200 hover:bg-sky-600/50 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Update Sistem
                </a>
            @endif

            <div class="pt-6 mt-6 border-t border-white/10">
                <a href="{{ route('admin.business.index') }}" class="flex items-center px-4 py-3 rounded-xl text-white/60 hover:bg-white/10 hover:text-white transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    Ganti Bisnis
                </a>
            </div>
        </nav>

        <div class="p-5 bg-sky-800/80 border-t border-sky-600/50 mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="flex items-center justify-center bg-rose-500 hover:bg-rose-600 text-white w-full py-2.5 rounded-xl text-sm font-bold transition-colors shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
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

    <style>
        /* Mempercantik Scrollbar di Sidebar */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.4); }
    </style>
</body>
</html>