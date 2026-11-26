<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $appSettings['theme_color'] ?? '#117e88' }}">
    <title>{{ $appSettings['business_name'] ?? config('netbill.name') }} · {{ config('netbill.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --theme: {{ $appSettings['theme_color'] ?? '#117e88' }}; }
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; -webkit-tap-highlight-color: transparent; }
        [x-cloak] { display: none !important; }
        .bg-theme { background-color: var(--theme); }
        .text-theme { color: var(--theme); }
        .bg-theme-soft { background-color: color-mix(in srgb, var(--theme) 10%, white); }
        .pb-safe { padding-bottom: max(0.5rem, env(safe-area-inset-bottom)); }
    </style>
    @include('partials.netbill_head')
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
@php
    $user = Auth::user();
    $tabs = array_values(array_filter([
        ['mobile.home', 'mobile.home', 'Beranda', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['mobile.collections.index', 'mobile.collections.*', 'Tagih', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ['mobile.deposits.create', 'mobile.deposits.create', 'Setor', 'M12 4v16m8-8H4'],
        ['mobile.deposits.index', 'mobile.deposits.index', 'Riwayat', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        in_array($user->role, ['admin', 'owner'], true) ? ['dashboard', 'dashboard', 'Admin', 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'] : null,
    ]));
@endphp

    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-slate-200/70">
        <div class="max-w-lg mx-auto flex items-center gap-3 px-4 h-14">
            <div class="w-9 h-9 rounded-full bg-theme text-white grid place-items-center font-bold shrink-0">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] text-slate-400 leading-tight truncate">{{ $appSettings['business_name'] ?? config('netbill.name') }}</div>
                <div class="font-bold text-[15px] leading-tight truncate">Halo, {{ $user->name }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="p-2 -mr-2 rounded-lg text-slate-400 hover:text-rose-600" aria-label="Keluar" title="Keluar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </header>

    <main class="nb-content max-w-lg mx-auto px-4 pt-5 pb-28">
        @include('partials.invitation_banner')
        @yield('content')
        @include('partials.netbill_footer')
    </main>

    <nav class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200/70 pb-safe">
        <div class="max-w-lg mx-auto grid" style="grid-template-columns: repeat({{ count($tabs) }}, minmax(0, 1fr))">
            @foreach($tabs as [$route, $pattern, $label, $icon])
                @php $act = request()->routeIs($pattern); @endphp
                <a href="{{ route($route) }}" class="flex flex-col items-center gap-0.5 pt-2 pb-1 text-[11px] {{ $act ? 'text-theme font-semibold' : 'text-slate-500' }}">
                    @if($route === 'mobile.deposits.create')
                        <span class="-mt-5 mb-0.5 w-11 h-11 rounded-full bg-theme text-white grid place-items-center shadow-lg shadow-slate-300 ring-4 ring-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="{{ $icon }}"></path></svg>
                        </span>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $act ? 2.2 : 1.8 }}" d="{{ $icon }}"></path></svg>
                    @endif
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>
</body>
</html>
