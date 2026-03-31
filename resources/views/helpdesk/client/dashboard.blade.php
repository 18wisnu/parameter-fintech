<x-app-layout>
    <div class="py-6 sm:py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            {{-- Header Profil --}}
            <div class="bg-indigo-700 rounded-3xl p-6 sm:p-10 text-white shadow-xl shadow-indigo-100 flex flex-col sm:flex-row justify-between items-center gap-6 relative overflow-hidden">
                <div class="relative z-10 text-center sm:text-left">
                    <h2 class="text-2xl sm:text-3xl font-black mb-1 italic">Halo, {{ auth()->user()->name }}!</h2>
                    <p class="text-indigo-100 text-sm font-medium opacity-90 tracking-wide">ID Pelanggan: #CUST-{{ str_pad($device->customer_id, 4, '0', STR_PAD_LEFT) }}</p>
                    <div class="mt-4 inline-flex items-center gap-2 bg-white/20 px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest backdrop-blur-sm">
                         <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                         Status: Layanan Aktif
                    </div>
                </div>
                <div class="relative z-10 flex flex-col items-center sm:items-end">
                    <p class="text-indigo-100 text-[10px] uppercase font-black tracking-widest opacity-60 mb-1">Nama Site / Lokasi</p>
                    <p class="text-xl font-bold">{{ $device->site ? $device->site->name : 'Tanjungpura' }}</p>
                </div>
                {{-- Decorative circles --}}
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-indigo-900/20 rounded-full blur-3xl"></div>
            </div>

            {{-- Info Utama Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Kartu Status Modem --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.040L3 14.535a12.003 12.003 0 007.618 10.535l1.382.465 1.382-.465A12.003 12.003 0 0021 14.535l-.382-8.509z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800">Status Modem</h4>
                            <p class="text-xs text-slate-400 font-medium">Informasi perangkat saat ini</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
                            <span class="text-xs font-bold text-slate-400 uppercase">Uptime</span>
                            <span class="text-xs font-mono font-bold text-slate-700 tracking-tight">{{ $liveInfo['uptime'] }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-slate-50 rounded-xl flex flex-col items-center">
                                <span class="text-[9px] font-black text-slate-400 uppercase mb-1">Suhu</span>
                                <span class="text-xs font-bold text-amber-600">{{ $liveInfo['temp'] }}°C</span>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl flex flex-col items-center">
                                <span class="text-[9px] font-black text-slate-400 uppercase mb-1">Mode</span>
                                <span class="text-[10px] font-black text-indigo-600">{{ $liveInfo['pon'] }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
                            <span class="text-xs font-bold text-slate-400 uppercase">IP Internet</span>
                            <span class="text-xs font-mono font-bold text-indigo-600 tracking-tight">{{ $device->ip_pppoe ?? 'Dialing...' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Kartu Daftar Perangkat Terhubung (FEATURE 1) --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition flex flex-col">
                    <div class="flex items-center justify-between mb-6">
                       <div class="flex items-center gap-4">
                            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl shadow-inner">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800">Perangkat Aktif</h4>
                                <p class="text-xs text-slate-400 font-medium">Terhubung ke Wi-Fi</p>
                            </div>
                       </div>
                       <span class="bg-indigo-600 text-white px-2.5 py-1 rounded-full text-[10px] font-black">{{ count($liveInfo['hosts']) }}</span>
                    </div>
                    
                    <div class="flex-grow max-h-40 overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                        @forelse($liveInfo['hosts'] as $host)
                        <div class="flex items-center gap-3 p-2.5 hover:bg-slate-50 rounded-xl transition border border-transparent hover:border-slate-100">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="flex-grow">
                                <p class="text-[11px] font-bold text-slate-700 truncate max-w-[150px]">{{ $host['name'] }}</p>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-x-3 gap-y-0.5">
                                    <p class="text-[9px] font-mono text-slate-400 tracking-wider">{{ $host['mac'] }}</p>
                                    <p class="text-[9px] font-bold text-indigo-500/70">{{ $host['ip'] ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="h-full flex flex-col items-center justify-center text-slate-300 py-4">
                            <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <p class="text-[10px] font-bold italic">Tidak ada perangkat lain.</p>
                        </div>
                        @endforelse
                    </div>

                    {{-- Tombol Refresh Perangkat Aktif --}}
                    <div class="mt-4 pt-4 border-t border-slate-50">
                        <form action="{{ route('helpdesk.diagnostic', $device->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition group">
                                <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Perbarui Daftar Perangkat
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Pengaturan Wi-Fi Mandiri (FEATURE 2) --}}
            <div class="bg-white rounded-[2.5rem] p-6 sm:p-10 shadow-sm border border-slate-100" x-data="{ showPass: false, openModal: false }">
                <div class="flex items-center gap-5 mb-8">
                    <div class="p-4 bg-amber-50 text-amber-600 rounded-3xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-black text-slate-800 italic">Ganti Wi-Fi Mandiri</h4>
                        <p class="text-sm text-slate-400 font-medium">Ubah nama atau sandi kapan pun Anda mau.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Info SSID & Password Saat Ini --}}
                    <div class="space-y-4">
                        <div class="bg-slate-50 p-5 rounded-3xl border border-slate-200/50">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Nama SSID Sekarang</span>
                                <span class="px-1.5 py-0.5 bg-indigo-100 text-indigo-700 text-[9px] font-black rounded uppercase">Live</span>
                            </div>
                            <p class="text-lg font-bold text-slate-700 font-mono">{{ $liveInfo['ssid'] }}</p>
                        </div>
                        <div class="bg-slate-50 p-5 rounded-3xl border border-slate-200/50">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Kata Sandi Sekarang</span>
                                <button @click="showPass = !showPass" class="text-indigo-600 text-[10px] font-bold hover:underline" x-text="showPass ? 'Sembunyikan' : 'Lihat'"></button>
                            </div>
                            <p class="text-lg font-bold text-slate-700 font-mono tracking-tighter" x-text="showPass ? '{{ $liveInfo['password'] }}' : '••••••••••••'"></p>
                        </div>
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100 flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-[10px] text-amber-700 font-medium leading-relaxed italic">Modem akan terputus sekitar 1 menit setelah perubahan nama Wi-Fi diterapkan untuk proses dial ulang.</p>
                        </div>
                    </div>

                    {{-- Form Perubahan --}}
                    <form action="{{ route('helpdesk.update-wifi') }}" method="POST" class="space-y-4" onsubmit="return confirm('Apakah Anda yakin ingin mengganti pengaturan Wi-Fi? Koneksi Anda akan terputus sementara.')">
                        @csrf
                        <input type="hidden" name="genieacs_id" value="{{ $device->genieacs_id }}">
                        
                        <div class="group">
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1 shadow-indigo-50">Nama Wi-Fi Baru</label>
                            <input type="text" name="ssid" placeholder="Contoh: Rumah Kita" class="w-full rounded-2xl border-slate-200 focus:ring-amber-500 focus:border-amber-500 py-3 text-sm font-bold placeholder:font-normal placeholder:opacity-50" required>
                        </div>
                        
                        <div class="group">
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-1">Kata Sandi Baru</label>
                            <input type="text" name="password" placeholder="Minimal 8 karakter" minlength="8" class="w-full rounded-2xl border-slate-200 focus:ring-amber-500 focus:border-amber-500 py-3 text-sm font-bold placeholder:font-normal placeholder:opacity-50" required>
                        </div>

                        <button type="submit" class="w-full bg-slate-900 text-white rounded-2xl py-4 font-black uppercase text-sm tracking-widest hover:bg-slate-800 transition shadow-lg shadow-slate-200 flex items-center justify-center gap-3 active:scale-95">
                            Update Modem Sekarang
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Footer Bantuan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-center">
                 <div class="p-5 bg-white rounded-3xl border border-slate-100 flex items-center justify-between gap-4">
                     <div class="text-left">
                        <h5 class="text-sm font-black text-slate-800">Butuh Bantuan Teknisi?</h5>
                        <p class="text-[10px] text-slate-400 font-medium">Buka laporan gangguan via WhatsApp.</p>
                     </div>
                     <a href="https://wa.me/628XXXXXXXXXX" target="_blank" class="px-5 py-2.5 bg-emerald-50 text-emerald-600 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition">Chat CS</a>
                 </div>
                 <div class="p-5 bg-white rounded-3xl border border-slate-100 flex items-center justify-between gap-4">
                     <div class="text-left">
                        <h5 class="text-sm font-black text-slate-800">Kualitas Sinyal (Rx)</h5>
                        <p class="text-[10px] text-slate-400 font-medium">Kesehatan kabel fiber optic Anda.</p>
                     </div>
                     <div class="flex items-center gap-2">
                        @php
                            $rxVal = (isset($device->rx_power) && $device->rx_power !== '-' && $device->rx_power !== '') ? floatval($device->rx_power) : -99;
                            if ($rxVal <= -30) { $col = 'bg-slate-900'; $label = 'LOSS'; }
                            elseif ($rxVal >= -22) { $col = 'bg-emerald-500'; $label = $device->rx_power . ' dBm'; }
                            elseif ($rxVal >= -27) { $col = 'bg-amber-500'; $label = $device->rx_power . ' dBm'; }
                            else { $col = 'bg-rose-500'; $label = $device->rx_power . ' dBm'; }
                        @endphp
                        <span class="text-xs font-bold text-slate-700 italic">{{ $label }}</span>
                        <span class="w-2.5 h-2.5 rounded-full {{ $col }}"></span>
                     </div>
                 </div>
            </div>

        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
    </style>
</x-app-layout>