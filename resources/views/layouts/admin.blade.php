<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="{{ $appSettings['theme_color'] }}">
    <title>{{ $appSettings['business_name'] }} · {{ config('netbill.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --theme: {{ $appSettings['theme_color'] }}; }
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; -webkit-tap-highlight-color: transparent; }
        [x-cloak] { display: none !important; }
        .bg-theme { background-color: var(--theme); }
        .text-theme { color: var(--theme); }
        .border-theme { border-color: var(--theme); }
        .bg-theme-soft { background-color: color-mix(in srgb, var(--theme) 10%, white); }
        .nav-scroll::-webkit-scrollbar { width: 4px; }
        .nav-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .pb-safe { padding-bottom: max(0.5rem, env(safe-area-inset-bottom)); }
    </style>
    @include('partials.netbill_head')
</head>
<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

@php
    $f = fn (string $key) => $appSettings['features'][$key] ?? true;
    $isOwner = $userRole === 'owner';
    $isManager = in_array($userRole, ['owner', 'admin'], true);

    $icons = [
        'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        'bill' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'money' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'wifi' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0',
        'cog' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'phone' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
        'book' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'menu' => 'M4 6h16M4 12h16M4 18h16',
        'building' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'logout' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
    ];

    $isField = in_array($userRole, ['owner', 'admin', 'teknisi'], true);
    $net = (bool) ($appSettings['network_enabled'] ?? false);

    $main = [
        ['dashboard', 'dashboard', 'Dashboard', 'home', $isManager],
        ['customers.index', 'customers.*', 'Pelanggan', 'users', $isManager && $f('customer_data')],
        ['invoices.index', 'invoices.*', 'Tagihan', 'bill', $isManager && $f('invoices')],
    ];

    $groups = [
        ['Keuangan', 'money', $isManager ? [
            ['reports.history', 'reports.history', 'Buku Kas', $f('transactions')],
            ['finance.cash.index', 'finance.cash.*', 'Tutup Kas Harian', true],
            ['finance.receivables.index', 'finance.receivables.*', 'Umur Piutang', $f('invoices')],
            ['finance.pl.index', 'finance.pl.*', 'Laba Rugi & Tutup Buku', true],
            ['reports.index', 'reports.index', 'Bagi Hasil', $f('profit_sharing')],
            ['admin.salaries.index', 'admin.salaries.*', 'Gaji Pegawai', $f('salary_management')],
            ['reports.reserve', 'reports.reserve', 'Dana Cadangan', $f('reserve_fund')],
            ['reports.vouchers', 'reports.vouchers', 'Analisa Voucher', $f('voucher_analysis')],
        ] : []],
        // Akses menu jaringan menyesuaikan peran (lihat routes/web.php)
        ['Jaringan', 'wifi', $net ? [
            ['network.vouchers.index', 'network.vouchers.*', 'Voucher Hotspot', $isManager],
            ['network.services.index', 'network.services.*', 'Layanan Internet', true],
            ['network.online.index', 'network.online.*', 'Pengguna Online', $isField],
            ['network.tickets.index', 'network.tickets.*', 'Tiket Gangguan', $isField],
            ['network.installations.index', 'network.installations.*', 'Pemasangan Baru', $isField],
            ['network.entities.index', 'network.entities.*', 'Reseller & Outlet', $isOwner],
            ['network.reports.index', 'network.reports.*', 'Laporan Jaringan', $isOwner],
        ] : []],
        ['Pengaturan', 'cog', [
            ['admin.profit_sharing.index', 'admin.profit_sharing.*', 'Master Setting', $isManager],
            ['admin.users.index', 'admin.users.*', 'Data Pegawai', $isManager && $f('staff_data')],
            ['subscription.index', 'subscription.*', 'Langganan NetBill', $isManager],
            ['admin.mixradius.edit', 'admin.mixradius.*', 'Server Jaringan', $isOwner],
            ['admin.audit.index', 'admin.audit.*', 'Jejak Audit', $isOwner],
            ['admin.logs.index', 'admin.logs.*', 'Log Sistem', $isOwner],
            ['admin.system.index', 'admin.system.*', 'Update Sistem', $isOwner],
        ]],
    ];

    // Navigasi bawah di HP: menu yang paling sering dipakai
    $bottom = array_values(array_filter([
        $isManager ? ['dashboard', 'dashboard', 'Beranda', 'home'] : ['mobile.home', 'mobile.home', 'Beranda', 'home'],
        $isManager && $f('customer_data') ? ['customers.index', 'customers.*', 'Pelanggan', 'users'] : null,
        $isManager && $f('invoices') ? ['invoices.index', 'invoices.*', 'Tagihan', 'bill'] : null,
        $isManager && $f('transactions') ? ['reports.history', 'reports.history', 'Buku Kas', 'book'] : null,
    ]));

    $roleLabel = ['owner' => 'Pemilik', 'admin' => 'Admin', 'teknisi' => 'Teknisi', 'kolektor' => 'Kolektor'][$userRole] ?? 'Anggota';
    $initial = mb_strtoupper(mb_substr($appSettings['business_name'], 0, 1));

    $link = 'flex items-center gap-3 px-3 py-2 rounded-xl text-[13.5px] font-medium transition-colors';
    $on = 'bg-theme-soft text-theme font-semibold';
    $off = 'text-slate-600 hover:bg-slate-100 hover:text-slate-900';
@endphp

    {{-- Bar atas (HP) --}}
    <header class="md:hidden fixed top-0 inset-x-0 z-40 h-14 bg-white/90 backdrop-blur border-b border-slate-200/70 flex items-center gap-3 px-4 print:hidden">
        <div class="w-8 h-8 rounded-lg bg-theme text-white grid place-items-center text-sm font-bold">{{ $initial }}</div>
        <div class="min-w-0 flex-1">
            <div class="font-bold text-[15px] leading-tight truncate">{{ $appSettings['business_name'] }}</div>
            <div class="text-[11px] text-slate-400 leading-tight">{{ $roleLabel }}</div>
        </div>
        <button @click="sidebarOpen = true" class="p-2 -mr-2 rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Buka menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons['menu'] }}"></path></svg>
        </button>
    </header>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 md:hidden" x-cloak></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed top-0 left-0 z-50 w-64 h-full bg-white border-r border-slate-200/70 transition-transform duration-300 md:translate-x-0 flex flex-col print:hidden">
        <div class="h-14 flex items-center gap-2 px-5 border-b border-slate-100">
            <img src="{{ asset('images/netbill-logo-light.png') }}" alt="{{ config('netbill.name') }} – Internet Billing" class="h-9 w-auto">
            <span class="sr-only">{{ config('netbill.name') }}</span>
            <button @click="sidebarOpen = false" class="ml-auto md:hidden p-1.5 rounded-lg text-slate-400 hover:bg-slate-100" aria-label="Tutup menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Usaha aktif --}}
        <a href="{{ route('admin.business.index') }}" class="mx-3 mt-3 flex items-center gap-3 p-2.5 rounded-xl border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50" title="Ganti usaha">
            <div class="w-9 h-9 rounded-lg bg-theme text-white grid place-items-center font-bold shrink-0">{{ $initial }}</div>
            <div class="min-w-0 flex-1">
                <div class="font-semibold text-sm leading-tight truncate">{{ $appSettings['business_name'] }}</div>
                <div class="text-[11px] text-slate-400">{{ $roleLabel }}</div>
            </div>
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
        </a>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5 nav-scroll">
            @foreach($main as [$route, $pattern, $label, $icon, $show])
                @if($show)
                <a href="{{ route($route) }}" class="{{ $link }} {{ request()->routeIs($pattern) ? $on : $off }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icons[$icon] }}"></path></svg>
                    {{ $label }}
                </a>
                @endif
            @endforeach

            @foreach($groups as [$groupLabel, $groupIcon, $items])
                @php
                    $visible = array_values(array_filter($items, fn ($i) => $i[3]));
                    $active = collect($visible)->contains(fn ($i) => request()->routeIs($i[1]));
                @endphp
                @continue(empty($visible))
                <div x-data="{ open: {{ $active ? 'true' : 'false' }} }" class="pt-0.5">
                    <button type="button" @click="open = !open" class="{{ $link }} w-full {{ $active ? 'text-slate-900 font-semibold' : $off }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icons[$groupIcon] }}"></path></svg>
                        <span class="flex-1 text-left">{{ $groupLabel }}</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    <div x-show="open" x-cloak x-transition.opacity class="mt-0.5 ml-[22px] pl-3 border-l border-slate-200 space-y-0.5">
                        @foreach($visible as [$route, $pattern, $label])
                            <a href="{{ route($route) }}" class="block px-3 py-1.5 rounded-lg text-[13px] {{ request()->routeIs($pattern) ? $on : $off }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="pt-3 mt-3 border-t border-slate-100 space-y-0.5">
                @if(auth()->user()?->is_superadmin)
                <a href="{{ route('platform.tenants.index') }}" class="{{ $link }} {{ request()->routeIs('platform.*') ? $on : $off }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icons['building'] }}"></path></svg>
                    Panel Pengelola
                </a>
                @endif
                <a href="{{ route('mobile.home') }}" class="{{ $link }} {{ request()->routeIs('mobile.*') ? $on : $off }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icons['phone'] }}"></path></svg>
                    Menu Setoran
                </a>
            </div>
        </nav>

        <div class="p-3 border-t border-slate-100 flex items-center gap-3">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 min-w-0 flex-1 p-1.5 rounded-xl hover:bg-slate-50" title="Profil">
                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 grid place-items-center text-xs font-bold shrink-0">{{ mb_strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 1)) }}</div>
                <div class="min-w-0">
                    <div class="text-sm font-semibold truncate">{{ auth()->user()->name ?? '' }}</div>
                    <div class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</div>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Keluar" aria-label="Keluar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icons['logout'] }}"></path></svg>
                </button>
            </form>
        </div>
    </aside>

    <main class="min-h-screen pt-14 pb-20 md:pt-0 md:pb-0 md:ml-64 print:ml-0 print:pt-0 print:pb-0">
        <div class="nb-content p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
            @php $sub = $appSettings['subscription'] ?? null; @endphp
            @if($sub && !request()->routeIs('subscription.*', 'platform.*'))
                @if(in_array($sub['status'], ['expired', 'suspended'], true))
                    <a href="{{ route('subscription.index') }}" class="print:hidden flex items-center gap-2 mb-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3 font-semibold">Langganan NetBill usaha ini tidak aktif. Buka halaman Langganan →</a>
                @elseif(in_array($sub['status'], ['trial', 'active'], true) && $sub['days_left'] !== null && $sub['days_left'] <= config('netbill.reminder_days'))
                    <a href="{{ route('subscription.index') }}" class="print:hidden flex flex-wrap items-center gap-x-2 mb-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3">
                        {{ $sub['status'] === 'trial' ? 'Masa coba' : 'Langganan' }} NetBill berakhir {{ $sub['days_left'] === 0 ? 'hari ini' : 'dalam ' . $sub['days_left'] . ' hari' }}. <b>Perpanjang sekarang →</b>
                    </a>
                @endif
            @endif
            @yield('content')
        </div>
        @include('partials.netbill_footer')
    </main>

    {{-- Navigasi bawah (HP) --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200/70 grid pb-safe print:hidden" style="grid-template-columns: repeat({{ count($bottom) + 1 }}, minmax(0, 1fr))">
        @foreach($bottom as [$route, $pattern, $label, $icon])
            @php $act = request()->routeIs($pattern); @endphp
            <a href="{{ route($route) }}" class="flex flex-col items-center gap-0.5 pt-2 pb-1 text-[11px] {{ $act ? 'text-theme font-semibold' : 'text-slate-500' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $act ? 2.2 : 1.8 }}" d="{{ $icons[$icon] }}"></path></svg>
                {{ $label }}
            </a>
        @endforeach
        <button @click="sidebarOpen = true" class="flex flex-col items-center gap-0.5 pt-2 pb-1 text-[11px] text-slate-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icons['menu'] }}"></path></svg>
            Menu
        </button>
    </nav>
</body>
</html>
