@extends('layouts.admin')

@section('content')
    <div class="mb-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Update Aplikasi</h2>
            <p class="text-slate-500 mt-2">Sinkronisasi kode sistem langsung dengan repository GitHub resmi.</p>
        </div>
        <div>
            <a href="{{ route('admin.system.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white border border-slate-200 text-slate-700 font-bold hover:bg-slate-50 transition shadow-sm text-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Periksa Pembaruan
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-5 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-2xl flex items-center gap-3 shadow-sm animate-fade-in-down">
        <svg class="w-6 h-6 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-5 bg-rose-50 border border-rose-100 text-rose-800 rounded-2xl flex items-center gap-3 shadow-sm animate-fade-in-down">
        <svg class="w-6 h-6 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('error') }}</span>
    </div>
    @endif

    @if(session('update_log'))
    <div x-data="{ showLog: true }" class="mb-8 bg-slate-900 text-slate-100 rounded-3xl p-6 shadow-xl border border-slate-800">
        <div class="flex justify-between items-center mb-3">
            <span class="text-xs font-mono font-bold text-sky-400 uppercase tracking-widest flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                Output Log Pembaruan Terakhir
            </span>
            <button @click="showLog = !showLog" class="text-xs text-slate-400 hover:text-white font-mono underline">
                <span x-text="showLog ? 'Sembunyikan' : 'Tampilkan'"></span>
            </button>
        </div>
        <pre x-show="showLog" class="text-xs font-mono bg-slate-950 p-4 rounded-2xl text-emerald-400 overflow-x-auto whitespace-pre-wrap max-h-60 custom-scrollbar">{{ session('update_log') }}</pre>
    </div>
    @endif

    <!-- Status Card Utama -->
    <div class="bg-white rounded-[2.5rem] p-1 shadow-sm border border-slate-100 mb-10 overflow-hidden">
        <div class="flex flex-col lg:flex-row items-center p-10 gap-10">
            <!-- Icon Status -->
            <div class="relative">
                <div class="w-40 h-40 rounded-full flex items-center justify-center {{ $hasUpdate ? 'bg-rose-50 text-rose-500 shadow-[0_0_50px_rgba(244,63,94,0.2)]' : 'bg-emerald-50 text-emerald-500 shadow-[0_0_50px_rgba(16,185,129,0.2)]' }}">
                    @if($hasUpdate)
                        <svg class="w-20 h-20 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    @else
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @endif
                </div>
                @if($hasUpdate)
                    <span class="absolute top-2 right-2 flex h-6 w-6">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-6 w-6 bg-rose-600 border-2 border-white"></span>
                    </span>
                @endif
            </div>

            <!-- Teks Status -->
            <div class="flex-1 text-center lg:text-left">
                @if($hasUpdate)
                    <span class="inline-block px-4 py-1.5 bg-rose-100 text-rose-600 rounded-full text-[10px] font-black uppercase tracking-widest mb-4">Pembaruan Tersedia</span>
                    <h3 class="text-3xl font-black text-slate-800 mb-3">Ada Versi Baru!</h3>
                    <p class="text-slate-500 leading-relaxed font-medium">Commit terbaru di remote GitHub: <code class="bg-rose-50 text-rose-600 font-bold px-2 py-0.5 rounded">#{{ $remoteCommit }}</code>. Klik tombol di samping untuk menarik update otomatis.</p>
                @else
                    <span class="inline-block px-4 py-1.5 bg-emerald-100 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest mb-4">Sistem Optimal</span>
                    <h3 class="text-3xl font-black text-emerald-700 mb-3">Aplikasi Terupdate</h3>
                    <p class="text-slate-500 leading-relaxed font-medium">Versi lokal (<code class="bg-slate-100 text-slate-700 font-bold px-2 py-0.5 rounded">#{{ $localCommit }}</code>) telah sinkron dengan branch <span class="font-bold text-slate-700">{{ $currentBranch }}</span>. Anda tetap dapat menarik ulang jika terdapat perubahan terbaru.</p>
                @endif
            </div>

            <!-- Tombol Aksi -->
            <div class="w-full lg:w-72">
                <form action="{{ route('admin.system.update') }}" method="POST" id="updateForm">
                    @csrf
                    <button type="submit" 
                        class="w-full py-6 {{ $hasUpdate ? 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-200' : 'bg-sky-600 hover:bg-sky-700 text-white shadow-sky-200' }} font-black rounded-3xl shadow-xl transition-all active:scale-95 flex items-center justify-center gap-3 group"
                        onclick="return confirmUpdate({{ $hasUpdate ? 'true' : 'false' }})">
                        <svg class="w-6 h-6 group-hover:rotate-180 transition-transform duration-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        {{ $hasUpdate ? 'Mulai Update' : 'Tarik Update Sekarang' }}
                    </button>
                    @if(!$hasUpdate)
                        <p class="text-[10px] text-slate-400 mt-4 text-center font-bold tracking-widest uppercase">Klik untuk Force Sync / Pull</p>
                    @else
                        <p class="text-[10px] text-rose-500 mt-4 text-center font-bold tracking-widest uppercase animate-pulse">Menunggu Tindakan Anda</p>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Detail Teknis (Untuk Developer/Advance) -->
    <div x-data="{ open: false }" class="bg-slate-50 rounded-3xl border border-slate-200 overflow-hidden">
        <button @click="open = !open" class="w-full p-6 flex justify-between items-center text-slate-500 hover:text-slate-800 transition-colors">
            <span class="text-xs font-black uppercase tracking-widest flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Informasi System (Detail Teknis)
            </span>
            <svg class="w-5 h-5 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        
        <div x-show="open" x-transition class="p-8 pt-0 grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-100 mt-6">
            <div class="space-y-4">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Internal Sistem</h4>
                <div class="flex justify-between p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-sm font-bold text-slate-500">Branch</span>
                    <span class="text-sm font-black text-slate-800">{{ $currentBranch }}</span>
                </div>
                <div class="flex justify-between p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-sm font-bold text-slate-500">Local Commit</span>
                    <span class="text-sm font-mono font-black text-sky-600">#{{ $localCommit }}</span>
                </div>
            </div>
            <div class="space-y-4">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Remote GitHub</h4>
                <div class="flex justify-between p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-sm font-bold text-slate-500">Pembaruan Terakhir</span>
                    <span class="text-sm font-black text-slate-800">{{ $lastUpdateAt }}</span>
                </div>
                <div class="flex justify-between p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-sm font-bold text-slate-500">Remote Commit</span>
                    <span class="text-sm font-mono font-black text-rose-500">#{{ $remoteCommit }}</span>
                </div>
            </div>
            <div class="md:col-span-2">
                <div class="p-5 bg-slate-900 rounded-2xl border border-slate-800 mt-2">
                    <span class="block text-[8px] font-black text-slate-500 uppercase tracking-[0.3em] mb-2">GitHub Change Message</span>
                    <p class="text-xs font-mono text-slate-300 leading-relaxed">"{{ $remoteMessage ?: 'No message available' }}"</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[100] flex flex-col items-center justify-center p-10 text-center">
        <div class="bg-white p-10 rounded-[3rem] shadow-2xl flex flex-col items-center max-w-sm">
            <div class="w-20 h-20 border-4 border-sky-100 border-t-sky-600 rounded-full animate-spin mb-8"></div>
            <h3 class="text-2xl font-black text-slate-800 mb-4">Mohon Tunggu...</h3>
            <p class="text-slate-500 text-sm leading-relaxed">Sistem sedang mengunduh data dan memperbarui struktur aplikasi. <b>Jangan tutup atau refresh halaman ini.</b></p>
        </div>
    </div>

    <script>
        function confirmUpdate(hasUpdate) {
            const msg = hasUpdate 
                ? 'Apakah Anda yakin ingin memperbarui aplikasi sekarang? Sistem akan melakukan sinkronisasi data dari GitHub.' 
                : 'Lakukan penarikan ulang (force pull) data terbaru dari GitHub sekarang?';
            if (confirm(msg)) {
                document.getElementById('loadingState').classList.remove('hidden');
                return true;
            }
            return false;
        }
    </script>
@endsection
