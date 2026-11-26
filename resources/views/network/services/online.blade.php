@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Pengguna Online',
    'subtitle' => 'Sesi yang sedang terhubung, dibedakan PPPoE, hotspot langganan & voucher.',
    'actions' => [['Layanan Internet', route('network.services.index')], ['Muat Ulang', request()->fullUrl(), true]],
])

@php
    $fmtBytes = function ($b) { $b = (float) $b; $u = ['B','KB','MB','GB','TB']; $i = 0; while ($b >= 1024 && $i < 4) { $b /= 1024; $i++; } return number_format($b, $i ? 1 : 0, ',', '.') . ' ' . $u[$i]; };
    $fmtUptime = function ($s) { $s = (int) $s; $d = intdiv($s, 86400); $h = intdiv($s % 86400, 3600); $m = intdiv($s % 3600, 60); return $d ? "{$d}h {$h}j" : ($h ? "{$h}j {$m}m" : "{$m}m"); };
    $badge = ['pppoe' => 'bg-indigo-50 text-indigo-700 ring-indigo-200', 'hotspot' => 'bg-teal-50 text-teal-700 ring-teal-200', 'voucher' => 'bg-amber-50 text-amber-700 ring-amber-200', 'other' => 'bg-slate-100 text-slate-600 ring-slate-200'];
    $dot = ['pppoe' => 'bg-indigo-500', 'hotspot' => 'bg-teal-500', 'voucher' => 'bg-amber-500', 'other' => 'bg-slate-400'];
    $tabs = ['' => ['Semua', $all]] + collect($labels)->mapWithKeys(fn ($l, $k) => [$k => [$l, (int) ($counts[$k] ?? 0)]])->filter(fn ($t, $k) => $t[1] > 0 || $k === $kind)->all();
@endphp

{{-- Ringkasan per jenis = tab filter --}}
<div class="flex gap-2 overflow-x-auto pb-1 mb-3 -mx-1 px-1">
    @foreach($tabs as $k => [$label, $n])
        @php $on = (string) $kind === (string) $k; @endphp
        <a href="{{ request()->fullUrlWithQuery(['kind' => $k ?: null, 'page' => null]) }}"
           class="shrink-0 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm ring-1 transition {{ $on ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-600 ring-slate-200 hover:ring-slate-300' }}">
            @if($k)<span class="w-2 h-2 rounded-full {{ $dot[$k] }}"></span>@endif
            {{ $label }}
            <span class="text-xs font-bold {{ $on ? 'text-white/70' : 'text-slate-400' }}">{{ $n }}</span>
        </a>
    @endforeach
</div>

<form method="GET" class="flex flex-wrap gap-2 mb-3">
    @if($kind)<input type="hidden" name="kind" value="{{ $kind }}">@endif
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari username, IP, atau MAC" class="flex-1 min-w-[180px] px-3 py-1.5 rounded-lg border-slate-200 text-sm">
    <input type="text" name="nas_ip" value="{{ request('nas_ip') }}" placeholder="IP router" class="w-32 px-3 py-1.5 rounded-lg border-slate-200 text-sm">
    <button class="px-4 py-1.5 rounded-lg bg-slate-900 text-white text-sm font-semibold">Cari</button>
</form>

<div class="bg-white rounded-2xl ring-1 ring-slate-200/70 overflow-hidden">
    <div class="overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th class="text-left">Pengguna</th>
                    <th class="text-left">Jenis</th>
                    <th class="text-left">IP / MAC</th>
                    <th class="text-left hidden lg:table-cell">Router</th>
                    <th class="text-right">Durasi</th>
                    <th class="text-right">Unduh / Unggah</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($page['items'] as $u)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0" title="Online"></span>
                            <div class="min-w-0">
                                <div class="font-mono font-semibold text-slate-800">{{ $u['username'] ?? '-' }}</div>
                                @if($u['customer_id'])
                                    <a href="{{ route('customers.show', $u['customer_id']) }}" class="text-xs text-sky-700 hover:underline">{{ $u['customer_name'] }}</a>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td><span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 whitespace-nowrap {{ $badge[$u['kind']] }}">{{ $labels[$u['kind']] }}</span></td>
                    <td class="font-mono text-xs text-slate-600 whitespace-nowrap">{{ $u['framed_ip'] ?? '-' }}<div class="text-slate-400">{{ $u['mac'] ?? '' }}</div></td>
                    <td class="font-mono text-xs text-slate-500 hidden lg:table-cell">{{ $u['nas_ip'] ?? '-' }}</td>
                    <td class="text-right text-slate-600 whitespace-nowrap">{{ isset($u['uptime']) ? $fmtUptime($u['uptime']) : '-' }}</td>
                    <td class="text-right text-slate-600 whitespace-nowrap">{{ isset($u['download']) ? $fmtBytes($u['download']) : '-' }}<div class="text-xs text-slate-400">{{ isset($u['upload']) ? '↑ ' . $fmtBytes($u['upload']) : '' }}</div></td>
                    <td class="text-right">
                        @if(!empty($u['username']))
                        <form action="{{ route('network.online.disconnect', $u['username']) }}" method="POST" onsubmit="return confirm('Putus sesi {{ $u['username'] }}? Pelanggan akan tersambung ulang otomatis.')">
                            @csrf
                            <button class="text-xs text-rose-600 hover:bg-rose-50 px-2 py-1 rounded-md font-semibold">Putus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-slate-400 !py-10">Tidak ada pengguna online{{ $kind ? ' untuk jenis ini' : '' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('network.partials.pagination')
</div>
<p class="text-xs text-slate-400 mt-2">Jenis ditentukan dari data layanan di server: username layanan PPP = PPPoE, layanan hotspot = hotspot langganan, selain itu = voucher hotspot.</p>
@endsection
