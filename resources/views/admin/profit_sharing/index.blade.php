@extends('layouts.admin')

@section('content')
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 animate-fade-in-down">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
    @endif

    <div class="mb-10 text-center lg:text-left">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Master Pengaturan Aplikasi</h2>
        <p class="text-slate-500 mt-2">Sesuaikan identitas bisnis, tema warna, dan fitur aktif aplikasi Anda.</p>
    </div>

    <form action="{{ route('admin.profit_sharing.update_settings') }}" method="POST" x-data="{ 
        features: {{ json_encode($enabledFeatures) }},
        toggle(key) { this.features[key] = !this.features[key] } 
    }">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-10">
            <!-- 1. Identitas Bisnis & Tema -->
            <div class="bg-white rounded-[2.5rem] p-8 lg:p-10 shadow-sm border border-slate-100 flex flex-col h-full">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 bg-sky-50 text-sky-600 rounded-2xl flex items-center justify-center shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-800">Identitas Bisnis</h3>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-widest mt-1">Branding & Look</p>
                    </div>
                </div>

                <div class="space-y-6 flex-1">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Usaha / Perusahaan</label>
                        <input type="text" name="business_name" value="{{ $businessName }}"
                            class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-slate-800 font-bold focus:ring-2 focus:ring-sky-500 transition-all" 
                            placeholder="Parameter Fintech">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tema Warna Utama</label>
                        <div class="flex gap-4 items-center">
                            <input type="color" name="theme_color" value="{{ $themeColor }}"
                                class="w-20 h-14 p-1 bg-white border border-slate-200 rounded-xl cursor-pointer">
                            <input type="text" value="{{ $themeColor }}" readonly
                                class="flex-1 px-6 py-4 bg-slate-50 border-none rounded-2xl text-slate-500 font-mono font-bold">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Feature Toggles -->
            <div class="bg-white rounded-[2.5rem] p-8 lg:p-10 shadow-sm border border-slate-100 flex flex-col h-full">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-800">Manajemen Fitur</h3>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-widest mt-1">Aktifkan Kebutuhan Bisnis</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([
                        'profit_sharing' => ['Bagi Hasil', 'Dividen & Laporan Laba'],
                        'reserve_fund' => ['Dana Cadangan', 'Tabungan & Dana Darurat'],
                        'salary_management' => ['Gaji & Payroll', 'Manajemen Gaji Pegawai'],
                        'voucher_analysis' => ['Analisa Voucher', 'Trend Penjualan Voucher'],
                        'customer_data' => ['Data Pelanggan', 'Manajemen Client/User'],
                        'invoices' => ['Tagihan / Invoice', 'Sistem Billing & Nota'],
                        'transactions' => ['Riwayat Kas', 'Log Pemasukan & Pengeluaran'],
                        'staff_data' => ['Data Pegawai', 'Manajemen SDM']
                    ] as $key => $info)
                    <div 
                        @click="features['{{ $key }}'] = !features['{{ $key }}']"
                        class="flex items-center p-4 rounded-3xl border transition-all cursor-pointer hover:shadow-md active:scale-[0.98]"
                        :class="features['{{ $key }}'] ? 'border-sky-300 bg-sky-50 shadow-sm' : 'border-slate-100 bg-slate-50 opacity-60'">
                        
                        <input type="hidden" name="feature_{{ $key }}" value="0">
                        <input type="checkbox" name="feature_{{ $key }}" value="1" class="hidden" :checked="features['{{ $key }}']">
                        
                        <div class="relative inline-flex items-center mr-4 pointer-events-none">
                            <div class="w-12 h-6 rounded-full transition-colors relative duration-300"
                                :class="features['{{ $key }}'] ? 'bg-sky-600' : 'bg-slate-300'">
                                <div class="absolute top-[2px] left-[2px] bg-white rounded-full h-5 w-5 transition-transform duration-300 shadow-sm"
                                    :class="features['{{ $key }}'] ? 'translate-x-6' : 'translate-x-0'"></div>
                            </div>
                        </div>
                        <div class="flex flex-col select-none pointer-events-none">
                            <span class="text-xs font-black text-slate-800 leading-none">{{ $info[0] }}</span>
                            <span class="text-[9px] text-slate-500 font-bold uppercase tracking-tight mt-1">{{ $info[1] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3. Dana Cadangan Row -->
        <div class="bg-white rounded-[2.5rem] p-8 lg:p-10 shadow-sm border border-slate-100 mb-10 overflow-hidden">
            <div class="flex flex-col lg:flex-row lg:items-center gap-10">
                <div class="flex-1">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-800">Persentase Simpanan</h3>
                    </div>
                    <p class="text-sm text-slate-500 leading-relaxed font-medium">Tentukan berapa persen laba bersih yang akan disisihkan sebagai dana cadangan atau tabungan usaha sebelum dibagikan ke stakeholder.</p>
                </div>

                <div class="w-full lg:w-96 flex flex-col sm:flex-row gap-4 items-end">
                    <div class="flex-1 w-full">
                        <div class="relative">
                            <input type="number" name="reserve_percentage" value="{{ $reservePercentage }}" step="0.01" min="0" max="100"
                                class="w-full pl-6 pr-12 py-5 bg-slate-50 border-none rounded-2xl text-slate-800 font-black focus:ring-2 focus:ring-sky-500 transition-all text-2xl">
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xl">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-start mb-14">
            <button type="submit" class="bg-slate-900 hover:bg-black text-white px-12 py-5 rounded-3xl font-black transition-all active:scale-95 shadow-2xl shadow-slate-200 flex items-center gap-3 group">
                <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Simpan Semua Perubahan
            </button>
        </div>
    </form>

    <!-- 2. Daftar Stakeholder -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Form Tambah -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm sticky top-10">
                <h3 class="text-lg font-black text-slate-800 mb-6">Tambah Penerima</h3>
                <form action="{{ route('admin.profit_sharing.stakeholder.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Nama Stakeholder</label>
                        <input type="text" name="name" required
                            class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl text-slate-800 font-bold focus:ring-2 focus:ring-sky-500 transition-all" 
                            placeholder="Contoh: Junaidi & Eka">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Persentase Jatah (%)</label>
                        <div class="relative">
                            <input type="number" name="percentage" required step="0.01" min="0" max="100"
                                class="w-full pl-6 pr-12 py-4 bg-slate-50 border-none rounded-2xl text-slate-800 font-bold focus:ring-2 focus:ring-sky-500 transition-all" 
                                placeholder="60">
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 font-bold">%</span>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-4 rounded-2xl font-black text-sm transition-all active:scale-95 shadow-lg shadow-emerald-100">
                        Tambah Stakeholder
                    </button>
                </form>

                <div class="mt-8 p-6 bg-amber-50 rounded-3xl border border-amber-100">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-[11px] text-amber-800 leading-relaxed font-bold uppercase tracking-tight">Total persentase semua stakeholder idealnya berjumlah <b>100%</b> dari sisa laba yang dapat didistribusikan.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- List Stakeholder -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2.5rem] p-1 shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-50">
                            <th class="text-left py-8 px-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Stakeholder</th>
                            <th class="text-center py-8 px-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Persentase</th>
                            <th class="text-right py-8 px-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($stakeholders as $item)
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td class="py-6 px-8">
                                <span class="font-black text-slate-700 block">{{ $item->name }}</span>
                            </td>
                            <td class="py-6 px-8 text-center">
                                <span class="inline-flex items-center px-4 py-2 bg-sky-50 text-sky-700 rounded-xl font-black text-sm">
                                    {{ $item->percentage }}%
                                </span>
                            </td>
                            <td class="py-6 px-8 text-right">
                                <div class="flex justify-end gap-2">
                                    <!-- Edit Trigger (Modal or Inline could be used, for simplicity let's use a small form or separate page, but here let's do a simple delete for now or suggest edit) -->
                                    <form action="{{ route('admin.profit_sharing.stakeholder.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus stakeholder ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="p-3 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    </div>
                                    <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">Belum ada stakeholder terdaftar</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @php
                $totalS = $stakeholders->sum('percentage');
            @endphp
            <div class="mt-6 flex justify-between items-center px-10">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Total Terdistribusi</span>
                <span class="text-xl font-black {{ $totalS == 100 ? 'text-emerald-500' : 'text-rose-500' }}">{{ $totalS }}%</span>
            </div>
        </div>
    </div>
@endsection
