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
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

@php
    $f = fn (string $key) => $appSettings['features'][$key] ?? true;
    $isOwner = $userRole === 'owner';
    $isManager = in_array($userRole, ['owner', 'admin'], true);

    // [route, pola aktif, label, ikon, tampil?]
    $icons = [
        'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        'bill' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'money' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'wifi' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0',
        'cog' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'phone' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
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
            ['admin.mixradius.edit', 'admin.mixradius.*', 'Server Jaringan', $isOwner],
            ['admin.audit.index', 'admin.audit.*', 'Jejak Audit', $isOwner],
            ['admin.logs.index', 'admin.logs.*', 'Log Sistem', $isOwner],
            ['admin.system.index', 'admin.system.*', 'Update Sistem', $isOwner],
        ]],
    ];

    $linkBase = 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors';
    $linkOn = 'bg-white text-slate-800 font-semibold shadow-sm';
    $linkOff = 'text-white/80 hover:bg-white/10 hover:text-white';
@endphp

    <header class="fixed top-0 left-0 right-0 h-14 bg-white border-b border-slate-100 z-40 flex items-center print:hidden gap-3 px-4 md:hidden">
        <button @click="sidebarOpen = true" class="p-2 -ml-2 text-slate-500 hover:bg-slate-100 rounded-lg" aria-label="Menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <span class="font-bold text-lg text-theme truncate">{{ $appSettings['business_name'] }}</span>
    </header>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-slate-900/50 z-50 md:hidden" x-cloak></div>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed top-0 left-0 z-50 w-60 h-full text-white transition-transform duration-300 md:translate-x-0 flex flex-col print:hidden" style="background-color: {{ $appSettings['theme_color'] }}">

        {{-- Unit bisnis aktif --}}
        <a href="{{ route('admin.business.index') }}" class="block px-4 py-4 border-b border-white/10 hover:bg-black/10" title="Ganti unit bisnis">
            <div class="font-bold text-base leading-tight truncate">{{ $appSettings['business_name'] }}</div>
            <div class="text-xs text-white/60 mt-0.5 flex items-center gap-1">
                {{ ['owner' => 'Pemilik', 'admin' => 'Admin', 'teknisi' => 'Teknisi', 'kolektor' => 'Kolektor'][$userRole] ?? 'Anggota' }}
                <span>· Ganti ⇄</span>
            </div>
        </a>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 custom-scrollbar">
            @foreach($main as [$route, $pattern, $label, $icon, $show])
                @if($show)
                <a href="{{ route($route) }}" class="{{ $linkBase }} {{ request()->routeIs($pattern) ? $linkOn : $linkOff }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$icon] }}"></path></svg>
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
                <div x-data="{ open: {{ $active ? 'true' : 'false' }} }" class="pt-1">
                    <button type="button" @click="open = !open" class="{{ $linkBase }} w-full {{ $active ? 'text-white font-semibold' : $linkOff }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$groupIcon] }}"></path></svg>
                        <span class="flex-1 text-left">{{ $groupLabel }}</span>
                        <svg class="w-4 h-4 transition-transform" :class="open && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                    <div x-show="open" x-cloak x-transition.opacity class="mt-1 ml-5 pl-3 border-l border-white/15 space-y-0.5">
                        @foreach($visible as [$route, $pattern, $label])
                            <a href="{{ route($route) }}" class="block px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs($pattern) ? $linkOn : $linkOff }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="pt-3 mt-3 border-t border-white/10">
                <a href="{{ route('mobile.home') }}" class="{{ $linkBase }} {{ request()->routeIs('mobile.*') ? $linkOn : $linkOff }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons['phone'] }}"></path></svg>
                    Menu Setoran
                </a>
            </div>
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="p-3 border-t border-white/10">
            @csrf
            <button class="{{ $linkBase }} w-full {{ $linkOff }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Keluar
            </button>
        </form>
    </aside>

    <main class="min-h-screen pt-14 md:pt-0 md:ml-60 print:ml-0 print:pt-0">
        <div class="p-4 md:p-8 max-w-7xl mx-auto">
            @yield('content')
        </div>
    </main>
</body>
</html>
