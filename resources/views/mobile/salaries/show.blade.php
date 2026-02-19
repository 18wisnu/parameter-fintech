@extends('layouts.mobile')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Slip Gaji</h2>
    <p class="text-gray-500 text-sm">Rincian pendapatan Anda bulan ini.</p>
</div>

<!-- Kartu Gaji Bulan Ini -->
@if($salary)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-purple-600 to-indigo-700 p-6 text-white">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs opacity-80 uppercase tracking-wider font-bold">{{ \Carbon\Carbon::parse($month)->format('F Y') }}</p>
                    <h3 class="text-lg font-bold">Total Pendapatan</h3>
                </div>
                <div class="bg-white/20 px-3 py-1 rounded-full text-[10px] font-bold uppercase">
                    {{ $salary->status == 'paid' ? 'Sudah Dibayar' : 'Pending' }}
                </div>
            </div>
            <h2 class="text-4xl font-black">Rp {{ number_format($salary->total_amount, 0, ',', '.') }}</h2>
        </div>
        
        <div class="p-5 space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-gray-50">
                <span class="text-gray-500 text-sm font-medium">Gaji Pokok</span>
                <span class="text-gray-800 font-bold">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center pb-3 border-b border-gray-50 text-emerald-600">
                <div class="flex flex-col">
                    <span class="text-sm font-bold">Bonus Aktivasi PPPoE</span>
                    <span class="text-[10px] opacity-70">Aktivasi pelanggan baru bulan ini</span>
                </div>
                <span class="font-bold">+Rp {{ number_format($salary->activation_bonus, 0, ',', '.') }}</span>
            </div>
            <div class="pt-2 flex justify-between items-center">
                <span class="text-gray-400 text-xs italic">
                    @if($salary->status == 'paid')
                        Dibayar pada {{ $salary->paid_at->format('d M Y H:i') }}
                    @else
                        Menunggu proses pencairan dari Admin
                    @endif
                </span>
            </div>
        </div>
    </div>
@else
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center mb-6">
        <div class="bg-amber-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-amber-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h3 class="font-bold text-amber-800 mb-1">Data Belum Tersedia</h3>
        <p class="text-amber-700 text-sm">Gaji untuk bulan <b>{{ \Carbon\Carbon::parse($month)->format('F Y') }}</b> belum digenerate oleh Admin.</p>
    </div>
@endif

<!-- Riwayat 6 Bulan Terakhir -->
<h3 class="font-bold text-gray-700 mb-4 text-lg">Riwayat Gaji Sebelumnya</h3>
<div class="space-y-3 pb-10">
    @forelse($history as $item)
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-50 flex justify-between items-center">
            <div>
                <p class="font-bold text-gray-800 text-sm">{{ \Carbon\Carbon::parse($item->month)->format('F Y') }}</p>
                <p class="text-xs {{ $item->status == 'paid' ? 'text-green-500' : 'text-orange-400' }} font-bold">
                    {{ $item->status == 'paid' ? 'Terbayar' : 'Proses' }}
                </p>
            </div>
            <div class="text-right">
                <p class="font-black text-gray-900">Rp {{ number_format($item->total_amount, 0, ',', '.') }}</p>
                @if($item->activation_bonus > 0)
                    <p class="text-[10px] text-emerald-500 font-bold">+{{ number_format($item->activation_bonus, 0, ',', '.') }} Bonus</p>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
            <p class="text-gray-400 text-sm italic">Belum ada riwayat gaji.</p>
        </div>
    @endforelse
</div>

<div class="fixed bottom-6 left-6 right-6">
    <a href="{{ route('mobile.home') }}" class="block w-full bg-gray-900 text-white text-center py-4 rounded-2xl font-bold shadow-xl active:scale-95 transition">
        Kembali ke Beranda
    </a>
</div>
@endsection
