@extends('layouts.admin')

@section('content')
    <div class="mb-10">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Pusat Pembaruan Sistem</h2>
        <p class="text-slate-500 mt-2">Dapatkan pembaruan fitur terbaru langsung dari repository GitHub.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Informasi Versi -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-8 border-b border-slate-50">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-sky-50 rounded-2xl flex items-center justify-center text-sky-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Versi Saat Ini</h3>
                            <p class="text-xs text-slate-400 font-medium">Informasi repository lokal</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Branch</span>
                            <span class="text-lg font-bold text-slate-700">{{ $currentBranch }}</span>
                        </div>
                        <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100">
                            <span class="block text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-1">Status</span>
                            <span class="text-lg font-bold text-emerald-600 flex items-center gap-2">
                                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                Stabil
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-slate-50/50">
                    <h4 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Apa yang terjadi saat Update?
                    </h4>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center flex-shrink-0 text-[10px] font-bold">1</div>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Sistem akan melakukan <span class="text-slate-700 font-bold">Git Pull</span> untuk mengambil kode terbaru dari GitHub.</p>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center flex-shrink-0 text-[10px] font-bold">2</div>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Sistem akan menjalankan <span class="text-slate-700 font-bold">Database Migration</span> untuk memperbarui struktur tabel jika ada perubahan.</p>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center flex-shrink-0 text-[10px] font-bold">3</div>
                            <p class="text-xs text-slate-500 leading-relaxed font-medium">Sistem membersihkan <span class="text-slate-700 font-bold">Cache</span> untuk memastikan fitur baru langsung aktif.</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div>
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-3xl p-8 shadow-2xl shadow-slate-200 border border-slate-700 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                
                <h3 class="text-xl font-bold text-white mb-2 relative z-10">🚀 Update Sekarang</h3>
                <p class="text-slate-400 text-sm mb-8 relative z-10 font-medium">Pastikan koneksi internet stabil sebelum melanjutkan.</p>

                <form action="{{ route('admin.system.update') }}" method="POST" id="updateForm" class="relative z-10">
                    @csrf
                    <button type="submit" 
                        class="w-full bg-sky-500 hover:bg-sky-400 text-white font-black py-4 rounded-2xl shadow-lg shadow-sky-900/20 transition-all hover:-translate-y-1 flex items-center justify-center gap-3 active:scale-95"
                        onclick="return confirmUpdate()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Mulai Perbarui
                    </button>
                    <p class="text-[10px] text-slate-500 mt-4 text-center font-bold tracking-widest uppercase">Pembaruan Manual (Stable)</p>
                </form>

                <!-- Loading State (Hidden by default) -->
                <div id="loadingState" class="hidden absolute inset-0 bg-slate-900/95 backdrop-blur-sm z-20 flex flex-col items-center justify-center p-8 text-center rounded-3xl">
                    <div class="w-16 h-16 border-4 border-sky-500/30 border-t-sky-500 rounded-full animate-spin mb-6"></div>
                    <h3 class="text-white font-bold mb-2 text-lg">Memproses Update</h3>
                    <p class="text-slate-400 text-xs">Sedang menarik data dari GitHub dan memperbarui sistem. Mohon jangan tutup halaman ini...</p>
                </div>
            </div>

            <div class="mt-8 bg-blue-50/50 p-6 rounded-3xl border border-blue-100">
                <p class="text-xs text-blue-700 leading-relaxed font-medium">
                    <span class="font-bold">Tips:</span> Sebaiknya lakukan update saat traffic pelanggan rendah untuk menghindari gangguan sementara.
                </p>
            </div>
        </div>
    </div>

    <script>
        function confirmUpdate() {
            if (confirm('Apakah Anda yakin ingin memperbarui sistem sekarang? Proses ini akan menimpa perubahan file lokal.')) {
                document.getElementById('loadingState').classList.remove('hidden');
                return true;
            }
            return false;
        }
    </script>
@endsection
