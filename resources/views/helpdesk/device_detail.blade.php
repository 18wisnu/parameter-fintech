<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="w-full sm:w-auto">
                <h2 class="font-bold text-xl sm:text-2xl text-slate-800 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span class="truncate">Device Monitor <span class="text-slate-300 font-light mx-1 sm:mx-2 hidden sm:inline">|</span> <span class="italic text-indigo-600 text-lg sm:text-xl block sm:inline mt-1 sm:mt-0">HelpDesk System</span></span>
                </h2>
            </div>
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto flex justify-center items-center gap-2 rounded-lg bg-slate-800 px-4 py-2.5 sm:py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-700 transition-all">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Alert Section --}}
            @if(session('success'))
                <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-200 flex items-start sm:items-center gap-3">
                    <svg class="h-5 w-5 text-emerald-400 flex-shrink-0 mt-0.5 sm:mt-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-200 flex items-start sm:items-center gap-3">
                    <svg class="h-5 w-5 text-red-400 flex-shrink-0 mt-0.5 sm:mt-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Main Profile Card --}}
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden mb-6 sm:mb-8">
                <div class="bg-slate-900 p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xl shadow-inner mt-1 sm:mt-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-white tracking-tight leading-none uppercase">
                                {{ $device->customer ? $device->customer->name : 'PELANGGAN BELUM TERDAFTAR' }}
                            </h3>
                            <p class="text-slate-400 text-xs sm:text-sm mt-2 sm:mt-1.5 flex items-start sm:items-center gap-1.5 font-medium leading-snug">
                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>{{ $device->customer ? ($device->customer->address ?: 'Alamat belum diisi') : 'Data alamat tidak tersedia' }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="inline-flex self-start md:self-auto items-center rounded-lg bg-white/10 px-3 py-1.5 sm:py-2 text-[10px] sm:text-xs font-bold text-white ring-1 ring-inset ring-white/20 uppercase tracking-widest">
                        Site: {{ $device->site ? $device->site->name : 'Pusat' }}
                    </div>
                </div>
                <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 bg-slate-50/30">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-1">Model / Brand</p>
                        <p class="font-bold text-slate-700 text-sm sm:text-base break-words">
                            <span class="p-1 sm:px-2 bg-white rounded border border-slate-200">{{ $info['model'] }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-1">Serial Number</p>
                        <p class="font-mono text-slate-600 bg-white px-2 py-1.5 rounded border border-slate-200 inline-block text-xs sm:text-sm break-all">{{ $info['sn'] }}</p>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-1">GenieACS Device ID</p>
                        <p class="text-[11px] sm:text-xs font-mono text-slate-500 break-all leading-relaxed">{{ $device->genieacs_id }}</p>
                    </div>
                </div>
            </div>

            {{-- Info Technical Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 mb-8 sm:mb-10">

                {{-- WAN Card --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 ring-1 ring-slate-200 shadow-sm flex flex-col">
                    <div class="flex items-center gap-3 mb-5 sm:mb-6">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9-9c1.657 0 3 4.03 3 9s-1.343 9-3 9m0-18c-1.657 0-3 4.03-3 9s1.343 9 3 9m-9-9h18"></path></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 tracking-tight text-base sm:text-lg">WAN & Network</h4>
                    </div>
                    <ul class="space-y-3 sm:space-y-4 flex-grow">
                        <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1 border-b border-slate-50 gap-1 sm:gap-0">
                            <span class="text-xs sm:text-sm text-slate-500 font-medium">IP TR069</span>
                            <span class="font-mono text-[11px] sm:text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded w-fit">{{ $info['ip_tr069'] }}</span>
                        </li>
                        <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1 border-b border-slate-50 gap-1 sm:gap-0">
                            <span class="text-xs sm:text-sm text-slate-500 font-medium">IP PPPoE</span>
                            <span class="font-mono text-[11px] sm:text-xs font-bold {{ $info['ip_pppoe'] === '-' ? 'text-red-500' : 'text-emerald-600' }} break-all">
                                {{ $info['ip_pppoe'] }}
                            </span>
                        </li>
                        @if($info['pppoe_user'] !== '-')
                        <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1 border-b border-slate-50 gap-1 sm:gap-0">
                            <span class="text-xs sm:text-sm text-slate-500 font-medium">User PPPoE</span>
                            <span class="font-mono text-[11px] sm:text-xs font-bold text-slate-700 italic underline decoration-slate-200 break-all">{{ $info['pppoe_user'] }}</span>
                        </li>
                        @endif
                        <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1 gap-1 sm:gap-0">
                            <span class="text-xs sm:text-sm text-slate-500 font-medium">Aktif Terakhir</span>
                            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-tighter">
                                {{ $info['last_inform'] ? \Carbon\Carbon::parse($info['last_inform'])->diffForHumans() : '-' }}
                            </span>
                        </li>
                    </ul>
                </div>

                {{-- WLAN Card --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 ring-1 ring-slate-200 shadow-sm flex flex-col">
                    <div class="flex items-center gap-3 mb-5 sm:mb-6">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.906 14.142 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 tracking-tight text-base sm:text-lg">WLAN & Access</h4>
                    </div>
                    <ul class="space-y-3 sm:space-y-4 flex-grow">
                        <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1 border-b border-slate-50 gap-1 sm:gap-0">
                            <span class="text-xs sm:text-sm text-slate-500 font-medium">SSID Aktif</span>
                            <span class="font-bold text-slate-800 text-xs sm:text-sm break-all">{{ $info['ssid'] }}</span>
                        </li>
                        <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1 border-b border-slate-50 gap-1 sm:gap-0">
                            <span class="text-xs sm:text-sm text-slate-500 font-medium">User Connect</span>
                            <span class="inline-flex items-center gap-1 bg-emerald-500 text-white px-2 py-0.5 rounded text-[10px] sm:text-[11px] font-bold w-fit">
                                {{ $info['active_devices'] }} <span class="font-normal opacity-80 uppercase">Dev</span>
                            </span>
                        </li>
                        <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1 gap-1 sm:gap-0">
                            <span class="text-xs sm:text-sm text-slate-500 font-medium">PON Mode</span>
                            <span class="text-[11px] sm:text-xs font-black text-slate-400 uppercase">{{ $info['pon_mode'] }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Hardware Card --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 ring-1 ring-slate-200 shadow-sm flex flex-col md:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-3 mb-5 sm:mb-6">
                        <div class="p-2 bg-rose-50 text-rose-600 rounded-lg flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 tracking-tight text-base sm:text-lg">Physical Health</h4>
                    </div>
                    <ul class="space-y-3 sm:space-y-4 flex-grow">
                        {{-- Di dalam kartu Hardware & Optik pada device-monitor.blade.php --}}
                            <li class="flex justify-between items-center py-1 border-b border-slate-50">
                                <span class="text-sm text-slate-500 font-medium">Redaman (Rx)</span>
                                @php
                                    $redaman = $info['redaman'];
                                    $rxVal = ($redaman !== '-') ? floatval($redaman) : -100;

                                    if ($rxVal <= -30) {
                                        $statusColor = 'text-slate-900'; // LOSS
                                    } elseif ($rxVal >= -20) {
                                        $statusColor = 'text-emerald-600'; // Hijau
                                    } elseif ($rxVal >= -26) {
                                        $statusColor = 'text-amber-600'; // Kuning
                                    } else {
                                        $statusColor = 'text-rose-600'; // Merah
                                    }
                                @endphp
                                <span class="font-bold text-sm {{ $statusColor }}">
                                    {{ ($rxVal <= -30) ? 'LOSS' : $redaman . ' dBm' }}
                                </span>
                            </li>
                        <li class="flex justify-between items-center py-1 border-b border-slate-50">
                            <span class="text-xs sm:text-sm text-slate-500 font-medium">Suhu Perangkat</span>
                            <span class="font-bold text-xs sm:text-sm text-amber-600">{{ $info['suhu'] }} <span class="text-[9px] sm:text-[10px] font-normal text-slate-400">°C</span></span>
                        </li>
                        <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1 gap-1 sm:gap-0">
                            <span class="text-xs sm:text-sm text-slate-500 font-medium">Uptime</span>
                            <span class="text-[11px] sm:text-xs font-bold text-slate-600 font-mono">{{ $info['uptime'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Action Toolbar --}}
            <div class="bg-slate-100/50 p-5 sm:p-6 rounded-2xl border border-slate-200" x-data="{ openWifi: false, openPppoe: false }">
                <div class="flex flex-col sm:flex-row flex-wrap justify-center gap-3 sm:gap-4">
                    
                    {{-- Tombol Wi-Fi --}}
                    <button @click="openWifi = true" class="w-full sm:w-auto flex justify-center items-center gap-3 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3.5 sm:py-4 rounded-xl text-sm sm:text-base font-bold shadow-md shadow-indigo-200 transition-all transform hover:-translate-y-0.5 active:scale-95">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.14a15 15 0 014.57-19.457m1.257 3.273a15.154 15.154 0 011.859 1.476m4.07 4.677a15.357 15.357 0 013.036 3.659m-1.692 1.433a15.181 15.181 0 01-1.154 3.036m-4.076 3.905A15.357 15.357 0 0112 21"></path></svg>
                        Konfigurasi Wi-Fi
                    </button>

                    {{-- Tombol PPPoE (Admin/Owner) --}}
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'owner')
                    <button @click="openPppoe = true" class="w-full sm:w-auto flex justify-center items-center gap-3 bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 px-6 py-3.5 sm:py-4 rounded-xl text-sm sm:text-base font-bold shadow-sm transition-all transform hover:-translate-y-0.5 active:scale-95">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        Update PPPoE
                    </button>
                    @endif

                    {{-- Tombol Diagnostik --}}
                    <form action="{{ route('helpdesk.diagnostic', $device->id) }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto flex justify-center items-center gap-3 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3.5 sm:py-4 rounded-xl text-sm sm:text-base font-bold shadow-md shadow-emerald-100 transition-all transform hover:-translate-y-0.5 active:scale-95">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.040L3 14.535a12.003 12.003 0 007.618 10.535l1.382.465 1.382-.465A12.003 12.003 0 0021 14.535l-.382-8.509z"></path></svg>
                            Diagnosis Cepat
                        </button>
                    </form>

                    {{-- Tombol Reboot --}}
                    <form action="{{ route('helpdesk.reboot', $device->id) }}" method="POST" onsubmit="return confirm('Mereset modem akan mematikan koneksi pelanggan sementara. Lanjutkan?')" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto flex justify-center items-center gap-3 bg-rose-500 hover:bg-rose-600 text-white px-6 py-3.5 sm:py-4 rounded-xl text-sm sm:text-base font-bold shadow-md shadow-rose-100 transition-all transform hover:-translate-y-0.5 active:scale-95">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Reboot Modem
                        </button>
                    </form>
                </div>

                {{-- Modal Ganti Wi-Fi --}}
                <div x-show="openWifi" style="display:none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                    <div x-show="openWifi" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                    <div @click.away="openWifi = false" x-show="openWifi" x-transition.scale.95 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-full">
                        <div class="bg-indigo-600 p-4 sm:p-5 text-white flex justify-between items-center shrink-0">
                            <h3 class="font-bold text-base sm:text-lg">📶 Update SSID & Sandi</h3>
                            <button @click="openWifi = false" class="text-white/60 hover:text-white text-2xl leading-none">&times;</button>
                        </div>
                        <div class="overflow-y-auto shrink p-4 sm:p-6">
                            <form action="{{ route('helpdesk.update-wifi') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="genieacs_id" value="{{ $device->genieacs_id }}">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama Wi-Fi (SSID)</label>
                                    <input type="text" name="ssid" value="{{ $info['ssid'] !== '-' ? $info['ssid'] : '' }}" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Password Baru</label>
                                    <input type="text" name="password" placeholder="Minimal 8 karakter" minlength="8" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                                </div>
                                <div class="pt-2 flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                                    <button type="button" @click="openWifi = false" class="w-full sm:w-auto px-4 py-2.5 sm:py-2 text-slate-500 hover:bg-slate-50 rounded-lg font-semibold border border-transparent hover:border-slate-200 transition">Batal</button>
                                    <button type="submit" class="w-full sm:w-auto bg-indigo-600 text-white px-6 py-2.5 sm:py-2 rounded-lg font-bold hover:bg-indigo-700 transition">Kirim Perintah</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Modal PPPoE --}}
                <div x-show="openPppoe" style="display:none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                    <div x-show="openPppoe" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                    <div @click.away="openPppoe = false" x-show="openPppoe" x-transition.scale.95 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-full">
                        <div class="bg-amber-500 p-4 sm:p-5 text-white flex justify-between items-center shrink-0">
                            <h3 class="font-bold text-base sm:text-lg">⚙️ Kredensial Dial PPPoE</h3>
                            <button @click="openPppoe = false" class="text-white/60 hover:text-white text-2xl leading-none">&times;</button>
                        </div>
                        <div class="overflow-y-auto shrink p-4 sm:p-6">
                            <form action="{{ route('helpdesk.update-pppoe') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="genieacs_id" value="{{ $device->genieacs_id }}">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Username PPPoE</label>
                                    <input type="text" name="pppoe_username" placeholder="Sesuai Mikrotik" class="w-full rounded-lg border-slate-300 focus:ring-amber-500 text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Password PPPoE</label>
                                    <input type="text" name="pppoe_password" placeholder="Sesuai Mikrotik" class="w-full rounded-lg border-slate-300 focus:ring-amber-500 text-sm" required>
                                </div>
                                <div class="bg-amber-50 p-3 rounded-lg text-xs text-amber-800 leading-relaxed italic border border-amber-200">
                                    <b>Catatan:</b> Modem akan otomatis melakukan 'Redial' setelah data dikirim.
                                </div>
                                <div class="pt-2 flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                                    <button type="button" @click="openPppoe = false" class="w-full sm:w-auto px-4 py-2.5 sm:py-2 text-slate-500 hover:bg-slate-50 rounded-lg font-semibold border border-transparent hover:border-slate-200 transition">Batal</button>
                                    <button type="submit" class="w-full sm:w-auto bg-amber-600 text-white px-6 py-2.5 sm:py-2 rounded-lg font-bold hover:bg-amber-700 transition">Simpan & Dial</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Diagnostics Panel (Owner Only) --}}
            @if(auth()->user()->role === 'owner')
            <div class="mt-8 sm:mt-12" x-data="{ open: false }">
                <button @click="open = !open" class="w-full group flex flex-col sm:flex-row justify-between sm:items-center bg-white border border-slate-200 rounded-xl px-4 sm:px-5 py-3 sm:py-4 text-xs sm:text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all gap-2 sm:gap-0 text-left">
                    <span class="flex items-center gap-2">
                        <span class="p-1 bg-slate-100 rounded group-hover:bg-indigo-100 transition-colors shrink-0">🔬</span>
                        Diagnostik Virtual Parameters <span class="hidden sm:inline">(Raw Data GenieACS)</span>
                    </span>
                    <span class="text-indigo-600 transition-transform duration-300 self-end sm:self-auto flex items-center gap-1" :class="open ? 'rotate-180' : ''">
                        <span class="sm:hidden text-[10px] text-slate-400 font-normal">Tampilkan Data</span> ▼
                    </span>
                </button>

                <div x-show="open" x-transition.origin.top.duration.300ms style="display:none;" class="mt-3 bg-white rounded-xl ring-1 ring-slate-200 shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="text-left px-4 sm:px-6 py-3 text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Parameter Key</th>
                                    <th class="text-left px-4 sm:px-6 py-3 text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest min-w-[150px]">Raw Value</th>
                                    <th class="text-right px-4 sm:px-6 py-3 text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-mono text-[10px] sm:text-xs">
                                @forelse($vpDebug as $vpName => $vpValue)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-4 sm:px-6 py-3 text-indigo-700 font-bold whitespace-nowrap">{{ $vpName }}</td>
                                    <td class="px-4 sm:px-6 py-3 text-slate-600 break-all">{{ $vpValue ?: '(null/empty)' }}</td>
                                    <td class="px-4 sm:px-6 py-3 text-right">
                                        @if($vpValue)
                                            <span class="text-emerald-500 font-bold whitespace-nowrap">● OK</span>
                                        @else
                                            <span class="text-rose-400 font-bold whitespace-nowrap">○ NULL</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-4 sm:px-6 py-8 sm:py-10 text-center text-slate-400 italic font-sans text-sm">No virtual parameters found for this device.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>