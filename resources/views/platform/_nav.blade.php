@php
    $tabs = [['platform.tenants.index', 'platform.tenants.*', 'Usaha'], ['platform.payments.index', 'platform.payments.*', 'Pembayaran'], ['platform.plans.index', 'platform.plans.*', 'Paket & Pengaturan']];
@endphp
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <div class="text-xs font-bold uppercase tracking-[0.2em] text-sky-600">Panel Pengelola {{ config('netbill.name') }}</div>
        <h2 class="text-2xl font-bold text-slate-800">{{ $title }}</h2>
    </div>
    <nav class="flex gap-1 bg-slate-100 rounded-xl p-1 text-sm">
        @foreach($tabs as [$r, $p, $l])
            <a href="{{ route($r) }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs($p) ? 'bg-white shadow-sm font-semibold text-slate-800' : 'text-slate-500 hover:text-slate-800' }}">{{ $l }}</a>
        @endforeach
    </nav>
</div>
