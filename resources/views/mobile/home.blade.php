@extends('layouts.mobile')

@section('content')

    @if(session('success'))
        <div class="bg-green-500 text-white p-3 rounded-lg mb-4 shadow-lg flex items-center justify-between animate-pulse">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-white font-bold">&times;</button>
        </div>
    @endif

    <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-xl p-5 text-white shadow-lg mb-6">
        <p class="text-sm opacity-90 mb-1">Total Setoran Hari Ini</p>
        <h2 class="text-3xl font-bold">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</h2>
        <p class="text-xs mt-2 bg-blue-800 inline-block px-2 py-1 rounded">Belum disetor ke Admin</p>
    </div>

    <h3 class="font-bold text-gray-700 mb-3 text-lg">Menu Utama</h3>
    <div class="grid grid-cols-2 gap-4">
        <a href="{{ route('mobile.deposits.create') }}" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center h-32 active:bg-gray-50">
            <div class="bg-green-100 p-3 rounded-full mb-2">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <span class="font-semibold text-gray-700">Setor Tunai</span>
        </a>
        <a href="#" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center h-32 active:bg-gray-50">
            <div class="bg-orange-100 p-3 rounded-full mb-2">
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            </div>
            <span class="font-semibold text-gray-700">Lapor Gangguan</span>
        </a>
        <a href="#" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center h-32 active:bg-gray-50">
            <div class="bg-purple-100 p-3 rounded-full mb-2">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <span class="font-semibold text-gray-700">Slip Gaji</span>
        </a>
    </div>

    <h3 class="font-bold text-gray-700 mt-6 mb-3 text-lg">Riwayat Hari Ini</h3>
    <div class="space-y-3 pb-20">
        @forelse($riwayatHariIni as $deposit)
            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <p class="font-semibold text-sm">{{ Str::limit($deposit->description, 25) }}</p>
                    <p class="text-xs text-gray-500">{{ $deposit->created_at->format('H:i') }} WIB 
                        @if($deposit->status == 'pending')
                            <span class="text-orange-500 font-bold ml-1">• Menunggu Admin</span>
                        @elseif($deposit->status == 'approved')
                            <span class="text-green-500 font-bold ml-1">• Diterima</span>
                        @endif
                    </p>
                </div>
                <span class="font-bold text-green-600 text-sm">+Rp {{ number_format($deposit->amount, 0, ',', '.') }}</span>
            </div>
        @empty
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <p class="text-gray-400 text-sm">Belum ada setoran hari ini.</p>
                <p class="text-xs text-gray-400">Ayo mulai kerja! 💪</p>
            </div>
        @endforelse
    </div>

@endsection