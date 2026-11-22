@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Pengguna Online',
    'subtitle' => 'Sesi yang sedang terhubung ke router saat ini.',
    'actions' => [['Layanan Internet', route('network.services.index')], ['Muat Ulang', request()->fullUrl(), true]],
])

<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari username, IP, atau MAC" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
    <input type="text" name="nas_ip" value="{{ request('nas_ip') }}" placeholder="IP router (opsional)" class="px-3 py-2 rounded-xl border-slate-200 text-sm">
    <button class="bg-slate-900 hover:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-widest py-2">Filter</button>
</form>

@php
    $fmtBytes = function ($b) { $b = (float) $b; $u = ['B','KB','MB','GB','TB']; $i = 0; while ($b >= 1024 && $i < 4) { $b /= 1024; $i++; } return number_format($b, $i ? 1 : 0, ',', '.') . ' ' . $u[$i]; };
    $fmtUptime = function ($s) { $s = (int) $s; $d = intdiv($s, 86400); return ($d ? $d . 'h ' : '') . gmdate('H:i:s', $s % 86400); };
@endphp

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                <tr>
                    <th class="px-4 py-3 text-left">Username</th>
                    <th class="px-4 py-3 text-left">IP</th>
                    <th class="px-4 py-3 text-left">MAC</th>
                    <th class="px-4 py-3 text-left">Router</th>
                    <th class="px-4 py-3 text-right">Durasi</th>
                    <th class="px-4 py-3 text-right">Download</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($page['items'] as $u)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono font-bold text-slate-700"><span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span>{{ $u['username'] ?? '-' }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $u['framed_ip'] ?? '-' }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $u['mac'] ?? '-' }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $u['nas_ip'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs text-slate-600 whitespace-nowrap">{{ isset($u['uptime']) ? $fmtUptime($u['uptime']) : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs text-slate-600 whitespace-nowrap">{{ isset($u['download']) ? $fmtBytes($u['download']) : '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        @if(!empty($u['username']))
                        <form action="{{ route('network.online.disconnect', $u['username']) }}" method="POST" onsubmit="return confirm('Putus sesi {{ $u['username'] }}? Pelanggan akan tersambung ulang otomatis.')">
                            @csrf
                            <button class="text-xs bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 px-3 py-1.5 rounded-lg font-bold">Putus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400 text-sm">Tidak ada pengguna online.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('network.partials.pagination')
</div>
@endsection
