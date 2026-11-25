@extends('layouts.mobile')

@section('content')
@include('partials.flash')
@php
    $net = (bool) ($appSettings['network_enabled'] ?? false);
    $field = in_array($userRole ?? null, ['owner', 'admin', 'teknisi'], true);
    $tiles = array_values(array_filter([
        [route('mobile.deposits.create'), 'Setor Tunai', 'emerald', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        [route('mobile.collections.index'), 'Daftar Tagih', 'rose', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        [route('mobile.deposits.index'), 'Riwayat', 'sky', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        [route('mobile.salary.show'), 'Slip Gaji', 'purple', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        $net && $field ? [route('network.tickets.index'), 'Tiket Gangguan', 'orange', 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'] : null,
        $net && $field ? [route('network.installations.index'), 'Pasang Baru', 'teal', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'] : null,
        $net && $field ? [route('network.online.index'), 'User Online', 'indigo', 'M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z'] : null,
        $net ? [route('network.services.index'), 'Cek Pelanggan', 'slate', 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'] : null,
    ]));
@endphp
{{-- kelas dinamis Tailwind: bg-emerald-50 text-emerald-600 bg-rose-50 text-rose-600 bg-sky-50 text-sky-600 bg-purple-50 text-purple-600 bg-orange-50 text-orange-600 bg-teal-50 text-teal-600 bg-indigo-50 text-indigo-600 bg-slate-100 text-slate-600 --}}

<section class="rounded-3xl bg-theme text-white p-5 shadow-lg shadow-slate-200 relative overflow-hidden">
    <div class="absolute -right-8 -top-10 w-36 h-36 rounded-full bg-white/10"></div>
    <p class="text-xs text-white/75 font-medium">Setoran hari ini</p>
    <p class="text-3xl font-extrabold tracking-tight mt-1">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</p>
    <p class="text-xs text-white/80 mt-3">
        {{ $riwayatHariIni->where('status', 'pending')->isNotEmpty() ? 'Ada setoran menunggu konfirmasi admin' : $riwayatHariIni->where('status', '!=', 'rejected')->count() . ' setoran · tercatat di buku kas' }}
    </p>
</section>

<section class="grid grid-cols-4 gap-x-2 gap-y-4 mt-6">
    @foreach($tiles as [$href, $label, $tone, $icon])
        <a href="{{ $href }}" class="flex flex-col items-center text-center gap-1.5 active:scale-95 transition">
            <span class="w-14 h-14 rounded-2xl grid place-items-center bg-{{ $tone === 'slate' ? 'slate-100' : $tone . '-50' }} text-{{ $tone }}-600">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"></path></svg>
            </span>
            <span class="text-[11.5px] font-medium text-slate-700 leading-tight">{{ $label }}</span>
        </a>
    @endforeach
</section>

<div class="flex items-center justify-between mt-8 mb-3">
    <h3 class="font-bold text-slate-900">Riwayat hari ini</h3>
    <a href="{{ route('mobile.deposits.index') }}" class="text-xs font-semibold text-theme">Lihat semua</a>
</div>

<div class="bg-white rounded-2xl border border-slate-200/70 divide-y divide-slate-100">
    @forelse($riwayatHariIni as $deposit)
        @php $st = ['approved' => ['Diterima', 'text-emerald-600 bg-emerald-50'], 'rejected' => ['Dibatalkan', 'text-rose-600 bg-rose-50']][$deposit->status] ?? ['Menunggu', 'text-amber-600 bg-amber-50']; @endphp
        <div class="flex items-center gap-3 p-3.5">
            <div class="w-10 h-10 rounded-xl grid place-items-center {{ $st[1] }}">
                @if($deposit->status === 'approved')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                @elseif($deposit->status === 'rejected')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-sm text-slate-800 truncate">{{ $deposit->description }}</p>
                <p class="text-[11px] text-slate-400">{{ $deposit->created_at->format('H:i') }} WIB · <span class="font-semibold {{ explode(' ', $st[1])[0] }}">{{ $st[0] }}</span></p>
            </div>
            <span class="font-bold text-sm {{ $deposit->status === 'rejected' ? 'text-slate-400 line-through' : 'text-slate-900' }}">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</span>
        </div>
    @empty
        <div class="text-center py-12 px-4">
            <p class="font-semibold text-slate-500 text-sm">Belum ada setoran hari ini</p>
            <a href="{{ route('mobile.deposits.create') }}" class="inline-block mt-3 px-4 py-2 rounded-xl bg-theme text-white text-sm font-semibold">Setor sekarang</a>
        </div>
    @endforelse
</div>
@endsection
