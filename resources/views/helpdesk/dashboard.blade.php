<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-800 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span class="truncate">{{ __('Dashboard Tanjungpura Net') }}</span>
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Sistem Manajemen Perangkat & Helpdesk</p>
            </div>
            <div class="flex items-center gap-3 bg-white px-3 sm:px-4 py-2 rounded-lg border border-slate-200 shadow-sm w-full sm:w-auto">
                <span class="text-xs sm:text-sm text-slate-500 font-medium">Role:</span>
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 sm:px-2.5 py-1 text-[10px] sm:text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 uppercase tracking-wider">
                    {{ auth()->user()->role ?? 'staff' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 rounded-md bg-emerald-50 p-4 border border-emerald-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 📊 Analytics Header --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                {{-- Legend & Summary --}}
                <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-200 flex flex-col justify-between">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-4">Total Device</p>
                        <h3 class="text-3xl font-black text-slate-800 italic leading-none">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="bg-emerald-500 p-5 rounded-[2rem] shadow-lg shadow-emerald-200 flex flex-col justify-between text-white border-b-4 border-emerald-700">
                        <p class="text-[10px] font-black uppercase tracking-widest leading-none mb-4 opacity-70">Sinyal Bagus</p>
                        <h3 class="text-3xl font-black italic leading-none">{{ $stats['good'] }}</h3>
                    </div>
                    <div class="bg-amber-500 p-5 rounded-[2rem] shadow-lg shadow-amber-200 flex flex-col justify-between text-white border-b-4 border-amber-700">
                        <p class="text-[10px] font-black uppercase tracking-widest leading-none mb-4 opacity-70">Warning</p>
                        <h3 class="text-3xl font-black italic leading-none">{{ $stats['warning'] }}</h3>
                    </div>
                    <div class="bg-rose-500 p-5 rounded-[2rem] shadow-lg shadow-rose-200 flex flex-col justify-between text-white border-b-4 border-rose-700">
                        <p class="text-[10px] font-black uppercase tracking-widest leading-none mb-4 opacity-70">Critical</p>
                        <h3 class="text-2xl font-black italic leading-none">{{ $stats['critical'] + $stats['offline'] }}</h3>
                    </div>
                </div>

                {{-- Pie Chart --}}
                <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-200 flex items-center justify-between gap-6 overflow-hidden">
                    <div class="flex-1">
                        <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-1">Network Health</p>
                        <p class="text-xs text-slate-400 font-medium">Real-time status modem aktif dari GenieACS.</p>
                        <div class="mt-4 flex items-center gap-2">
                             @php $percent = $stats['total'] > 0 ? round(($stats['good'] / $stats['total']) * 100) : 0; @endphp
                             <span class="text-4xl font-black italic {{ $percent > 80 ? 'text-emerald-500' : 'text-amber-500' }}">{{ $percent }}%</span>
                             <span class="text-[10px] font-bold text-slate-400 uppercase bg-slate-100 px-2 py-1 rounded-full leading-none">Optimal</span>
                        </div>
                    </div>
                    <div class="w-24 h-24 sm:w-28 sm:h-28 flex-shrink-0 relative">
                        <canvas id="healthChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Main Card --}}
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-slate-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-50/50">
                    <h3 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                        Daftar Perangkat
                    </h3>

                    @if(auth()->user()->role !== 'client')
                    <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                        <form action="{{ route('helpdesk.cleanup-discovery') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full md:w-auto flex justify-center items-center gap-x-2 rounded-lg bg-rose-50 px-4 py-2 sm:py-2.5 text-sm font-bold text-rose-700 border border-rose-200 hover:bg-rose-100 transition-all" onclick="return confirm('Hapus semua perangkat Discovery (Unknown)?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Bersihkan Discovery
                            </button>
                        </form>

                        <form action="{{ route('helpdesk.sync') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full md:w-auto flex justify-center items-center gap-x-2 rounded-lg bg-indigo-600 px-4 py-2 sm:py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Tarik Data GenieACS
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-slate-600 bg-white px-3 py-1.5 rounded-lg border border-slate-200 w-full md:w-auto justify-center">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Auto-Sync Aktif
                    </div>
                    @endif
                </div>
                
                {{-- Tabel Area (Desktop) --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Pelanggan</th>
                                <th scope="col" class="px-6 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Site</th>
                                <th scope="col" class="px-6 py-5 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">SSID WiFi</th>
                                <th scope="col" class="px-6 py-5 text-center text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Sinyal Rx</th>
                                <th scope="col" class="px-6 py-5 text-center text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">User</th>
                                <th scope="col" class="px-6 py-5 text-right text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($devices as $device)
                            <tr class="hover:bg-slate-50/50 transition-colors group" x-data="{ openWifiModal: false, openCustomerModal: false }">
                                <td class="px-6 py-5">
                                    <div class="font-bold {{ $device->customer ? 'text-slate-900' : 'text-rose-500 italic' }} text-base leading-tight">
                                        {{ $device->customer ? $device->customer->name : 'Belum Terdaftar' }}
                                    </div>
                                    <div class="text-xs text-slate-400 mt-1 font-medium truncate max-w-[250px]">
                                        {{ $device->customer ? $device->customer->address : 'Klik daftar untuk lengkapi profil' }}
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-500 uppercase tracking-widest leading-none">
                                        {{ $device->site ? $device->site->name : 'Pusat' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg inline-block font-mono">
                                        {{ $device->ssid }}
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @php
                                        $rx = $device->rx_power;
                                        $rxVal = (isset($rx) && $rx !== '-' && $rx !== '') ? floatval($rx) : null;
                                        
                                        if ($rxVal === null || $rxVal === 0) {
                                            $col = 'bg-slate-400'; $txt = 'text-slate-400';
                                        } elseif ($rxVal <= -30) {
                                            $col = 'bg-slate-900'; $txt = 'text-slate-900';
                                        } elseif ($rxVal >= -22) {
                                            $col = 'bg-emerald-500'; $txt = 'text-emerald-600';
                                        } elseif ($rxVal >= -27) {
                                            $col = 'bg-amber-500'; $txt = 'text-amber-600';
                                        } else {
                                            $col = 'bg-rose-500'; $txt = 'text-rose-600';
                                        }
                                    @endphp
                                    <div class="flex items-center justify-center gap-3">
                                         <span class="relative flex h-4 w-4">
                                            @if($rxVal !== null && $rxVal > -30)
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $col }} opacity-75"></span>
                                            @endif
                                            <span class="relative inline-flex rounded-full h-4 w-4 {{ $col }}"></span>
                                         </span>
                                         <span class="text-sm font-black italic tracking-tighter {{ $txt }}">{{ $rx !== '-' ? $rx : 'LOSS' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-600">
                                        {{ $device->active_devices }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2.5">
                                        <button @click="openCustomerModal = true" class="p-2 text-slate-400 hover:text-indigo-600 transition" title="Edit Customer">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button @click="openWifiModal = true" class="p-2 text-slate-400 hover:text-amber-500 transition" title="WiFi Config">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.906 14.142 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                                        </button>
                                        <a href="{{ route('helpdesk.detail', $device->id) }}" class="p-2.5 bg-slate-100 text-slate-700 hover:bg-slate-900 hover:text-white rounded-xl transition">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </div>
                                    @include('components.helpdesk-modals')
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Dashboard View (Mobile - Card Layout) --}}
                <div class="lg:hidden p-4 space-y-5 bg-slate-50/50">
                    @foreach ($devices as $device)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ openWifiModal: false, openCustomerModal: false }">
                        <div class="p-5 flex justify-between items-start border-b border-slate-50">
                            <div>
                                <h4 class="font-black text-slate-900 uppercase italic text-base leading-tight">
                                    {{ $device->customer ? $device->customer->name : 'Unregistered' }}
                                </h4>
                                <div class="mt-2 flex gap-2 flex-wrap">
                                     <span class="text-[10px] font-black bg-slate-100 px-2 py-1 rounded text-slate-500 uppercase tracking-widest">{{ $device->site ? $device->site->name : 'Pusat' }}</span>
                                     <span class="text-[10px] font-black bg-indigo-50 px-2 py-1 rounded text-indigo-600 uppercase tracking-widest">{{ $device->ssid }}</span>
                                </div>
                            </div>
                            @php
                                $rx = $device->rx_power;
                                $rxVal = (isset($rx) && $rx !== '-' && $rx !== '') ? floatval($rx) : null;
                                if ($rxVal === null || $rxVal === 0) { $col = 'bg-slate-400'; $txt = 'text-slate-400'; }
                                elseif ($rxVal <= -30) { $col = 'bg-slate-900'; $txt = 'text-slate-900'; }
                                elseif ($rxVal >= -22) { $col = 'bg-emerald-500'; $txt = 'text-emerald-600'; }
                                elseif ($rxVal >= -27) { $col = 'bg-amber-500'; $txt = 'text-amber-600'; }
                                else { $col = 'bg-rose-500'; $txt = 'text-rose-600'; }
                            @endphp
                            <div class="flex items-center gap-2 bg-slate-50 px-2.5 py-1.5 rounded-full border border-slate-100">
                                <span class="relative flex h-3 w-3">
                                    @if($rxVal !== null && $rxVal > -30)
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $col }} opacity-75"></span>
                                    @endif
                                    <span class="relative inline-flex rounded-full h-3 w-3 {{ $col }}"></span>
                                </span>
                                <span class="text-xs font-black {{ $txt }} italic">{{ $rx !== '-' ? $rx : 'OFF' }}</span>
                            </div>
                        </div>
                        <div class="p-4 bg-white flex items-center justify-between">
                             <div class="flex items-center gap-2">
                                <span class="text-[11px] text-slate-400 font-black uppercase tracking-widest">Klien Wifi:</span>
                                <span class="bg-indigo-500 text-white px-2.5 py-1 rounded-lg text-xs font-black">{{ $device->active_devices }}</span>
                             </div>
                             <div class="flex gap-2">
                                 <button @click="openCustomerModal = true" class="p-2.5 bg-slate-100 rounded-xl text-slate-500 active:scale-95 transition">
                                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                 </button>
                                 <button @click="openWifiModal = true" class="p-2.5 bg-slate-100 rounded-xl text-slate-500 active:scale-95 transition">
                                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.906 14.142 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                                 </button>
                                 <a href="{{ route('helpdesk.detail', $device->id) }}" class="p-2.5 bg-slate-900 text-white rounded-xl active:scale-95 transition">
                                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                 </a>
                             </div>
                             @include('components.helpdesk-modals')
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('healthChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Bagus', 'Warning', 'Critical'],
                    datasets: [{
                        data: [{{ $stats['good'] }}, {{ $stats['warning'] }}, {{ $stats['critical'] + $stats['offline'] }}],
                        backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    cutout: '75%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
    @endpush
</x-app-layout>