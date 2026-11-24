@extends('layouts.mobile')

@section('content')

    @if(session('success'))
        <div class="bg-emerald-500 text-white p-4 rounded-2xl mb-6 shadow-lg shadow-emerald-200/50 flex items-center justify-between animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-white font-bold">&times;</button>
        </div>
    @endif

    <div class="bg-gradient-to-br from-sky-500 to-sky-700 rounded-[2rem] p-7 text-white shadow-xl shadow-sky-200/50 mb-10 relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-sky-400/20 rounded-full blur-2xl"></div>
        
        <p class="text-[10px] opacity-80 uppercase tracking-[0.2em] font-bold mb-1.5">Total Setoran Hari Ini</p>
        <h2 class="text-4xl font-bold tracking-tight">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</h2>
        <div class="flex items-center mt-5">
            @if($riwayatHariIni->where('status', 'pending')->isNotEmpty())
                <span class="text-[10px] bg-sky-800/40 backdrop-blur-sm px-4 py-1.5 rounded-full border border-white/20 font-bold tracking-wide">Ada setoran menunggu konfirmasi admin</span>
            @else
                <span class="text-[10px] bg-sky-800/40 backdrop-blur-sm px-4 py-1.5 rounded-full border border-white/20 font-bold tracking-wide">Tercatat di buku kas</span>
            @endif
        </div>
    </div>

    <div class="flex items-center justify-between mb-5 px-1">
        <h3 class="font-bold text-slate-800 text-xl tracking-tight">Menu Utama</h3>
        <span class="text-[10px] text-sky-600 font-bold uppercase tracking-[0.15em] bg-sky-50 px-3 py-1 rounded-full">Layanan</span>
    </div>
    
    <div class="grid grid-cols-2 gap-5 mb-10">
        <a href="{{ route('mobile.deposits.create') }}" class="group bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col items-center justify-center h-40 active:scale-95 transition-all hover:shadow-lg hover:border-emerald-100">
            <div class="bg-emerald-50 p-4 rounded-2xl mb-3 group-hover:bg-emerald-100 transition-colors">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <span class="font-bold text-slate-700 text-sm">Setor Tunai</span>
        </a>
        @php
            $net = (bool) ($appSettings['network_enabled'] ?? false);
            $field = in_array($userRole ?? null, ['owner', 'admin', 'teknisi'], true);
            $tiles = array_filter([
                [route('mobile.collections.index'), 'Daftar Tagih', 'rose', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                $net && $field ? [route('network.tickets.index'), 'Tiket Gangguan', 'orange', 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'] : null,
                $net && $field ? [route('network.installations.index'), 'Pemasangan Baru', 'teal', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'] : null,
                $net && $field ? [route('network.online.index'), 'Pengguna Online', 'indigo', 'M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z'] : null,
                $net ? [route('network.services.index'), 'Cek Pelanggan', 'sky', 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'] : null,
            ]);
        @endphp
        {{-- kelas dinamis untuk Tailwind: bg-rose-50 text-rose-600 bg-orange-50 text-orange-600 bg-teal-50 text-teal-600 bg-indigo-50 text-indigo-600 bg-sky-50 text-sky-600 --}}
        @foreach($tiles as [$href, $label, $tone, $icon])
        <a href="{{ $href }}" class="group bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col items-center justify-center h-40 active:scale-95 transition-all hover:shadow-lg">
            <div class="bg-{{ $tone }}-50 p-4 rounded-2xl mb-3">
                <svg class="w-8 h-8 text-{{ $tone }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path></svg>
            </div>
            <span class="font-bold text-slate-700 text-sm text-center leading-tight">{{ $label }}</span>
        </a>
        @endforeach
        <a href="{{ route('mobile.salary.show') }}" class="group bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col items-center justify-center h-40 active:scale-95 transition-all hover:shadow-lg hover:border-purple-100">
            <div class="bg-purple-50 p-4 rounded-2xl mb-3 group-hover:bg-purple-100 transition-colors">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <span class="font-bold text-slate-700 text-sm">Slip Gaji</span>
        </a>
        <a href="{{ route('mobile.deposits.index') }}" class="group bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col items-center justify-center h-40 active:scale-95 transition-all hover:shadow-lg hover:border-sky-100">
            <div class="bg-sky-50 p-4 rounded-2xl mb-3 group-hover:bg-sky-100 transition-colors">
                <svg class="w-8 h-8 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <span class="font-bold text-slate-700 text-sm">Riwayat</span>
        </a>
    </div>

    <div class="flex items-center justify-between mb-5 px-1">
        <h3 class="font-bold text-slate-800 text-xl tracking-tight">Riwayat Hari Ini</h3>
        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Terbaru</span>
    </div>

    <div class="space-y-4">
        @forelse($riwayatHariIni as $deposit)
            <div class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 flex justify-between items-center transition-all active:bg-slate-50">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl {{ $deposit->status == 'approved' ? 'bg-emerald-50 text-emerald-500' : ($deposit->status == 'rejected' ? 'bg-rose-50 text-rose-500' : 'bg-orange-50 text-orange-500') }} flex items-center justify-center text-xl shadow-inner">
                        @if($deposit->status == 'approved')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-[15px]">{{ Str::limit($deposit->description, 20) }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5 font-bold uppercase tracking-[0.1em] flex items-center gap-1.5">
                            {{ $deposit->created_at->format('H:i') }} WIB 
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            @if($deposit->status == 'pending')
                                <span class="text-orange-500">Menunggu</span>
                            @elseif($deposit->status == 'approved')
                                <span class="text-emerald-500">Diterima</span>
                            @elseif($deposit->status == 'rejected')
                                <span class="text-rose-500">Dibatalkan</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="font-bold text-slate-900 text-base">Rp {{ number_format($deposit->amount, 0, ',', '.') }}</span>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-[2.5rem] border border-dashed border-slate-200">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                    <span class="text-3xl">🏜️</span>
                </div>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em]">Belum ada riwayat</p>
                <p class="text-xs text-slate-300 mt-2">Mulai lakukan setoran hari ini! 💪</p>
            </div>
        @endforelse
    </div>

@endsection