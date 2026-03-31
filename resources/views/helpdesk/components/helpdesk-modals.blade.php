{{-- ======================================= --}}
{{-- MODAL KELOLA PELANGGAN & SITE --}}
{{-- ======================================= --}}
<div x-show="openCustomerModal" style="display: none;" class="relative z-50">
    <div x-show="openCustomerModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="openCustomerModal" x-transition.scale.95 @click.away="openCustomerModal = false" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md w-full">
                
                <div class="bg-white p-5 sm:p-6">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">Kelola Pelanggan & Site</h3>
                        <button @click="openCustomerModal = false" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-full p-1 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Form 1: Data Pelanggan --}}
                    <div class="mb-5">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">1. Identitas Pelanggan</h4>
                        <form action="{{ route('helpdesk.register-customer') }}" method="POST">
                            @csrf
                            <input type="hidden" name="device_id" value="{{ $device->id }}">
                            
                            <div class="mb-3">
                                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ $device->customer->name ?? '' }}" class="w-full rounded-lg border-slate-300 focus:ring-emerald-500 text-sm" required>
                            </div>
                            
                            {{-- INPUT BARU: Nomor HP & Email --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1">Nomor WA / HP</label>
                                    <input type="text" name="phone" value="{{ $device->customer->phone ?? '' }}" placeholder="Contoh: 08123456789" class="w-full rounded-lg border-slate-300 focus:ring-emerald-500 text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1">Email <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                    <input type="email" name="email" value="{{ $device->customer->email ?? '' }}" placeholder="email@contoh.com" class="w-full rounded-lg border-slate-300 focus:ring-emerald-500 text-sm">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1">Alamat Pemasangan</label>
                                <textarea name="address" rows="2" class="w-full rounded-lg border-slate-300 focus:ring-emerald-500 text-sm">{{ $device->customer->address ?? '' }}</textarea>
                            </div>

                            {{-- INPUT KOORDINAT --}}
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Latitude</label>
                                    <input type="text" name="latitude" value="{{ $device->customer->latitude ?? '' }}" placeholder="-0.0263" class="w-full rounded-lg border-slate-300 focus:ring-emerald-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Longitude</label>
                                    <input type="text" name="longitude" value="{{ $device->customer->longitude ?? '' }}" placeholder="109.3425" class="w-full rounded-lg border-slate-300 focus:ring-emerald-500 text-sm">
                                </div>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2 sm:py-1.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-500 transition">Simpan & Buat Akun Client</button>
                            </div>
                        </form>
                    </div>

                    {{-- Form 2: Pindah Site & ODP --}}
                    <div>
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">2. Topologi Jaringan (OLT & ODP)</h4>
                        <form action="{{ route('helpdesk.update-site') }}" method="POST">
                            @csrf
                            <input type="hidden" name="device_id" value="{{ $device->id }}">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Parent OLT</label>
                                    <select name="site_id" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 text-sm font-bold">
                                        <option value="">-- Tanpa OLT --</option>
                                        @foreach($sites as $site)
                                            <option value="{{ $site->id }}" {{ $device->site_id == $site->id ? 'selected' : '' }}>
                                                {{ $site->name }} ({{ $site->business_name ?? 'Pusat' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Titik ODP (Splitter)</label>
                                    <select name="odp_id" class="w-full rounded-lg border-slate-300 focus:ring-amber-500 text-sm font-bold">
                                        <option value="">-- Tanpa ODP / Direct --</option>
                                        @foreach($odps as $odp)
                                            <option value="{{ $odp->id }}" {{ $device->odp_id == $odp->id ? 'selected' : '' }}>
                                                {{ $odp->name }} ({{ $odp->site->name ?? 'Headend' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end mt-4">
                                <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-lg bg-indigo-600 px-4 py-2 sm:py-1.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 transition">Update Topologi</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ======================================= --}}
{{-- MODAL GANTI WIFI --}}
{{-- ======================================= --}}
<div x-show="openWifiModal" style="display: none;" class="relative z-50">
    <div x-show="openWifiModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="openWifiModal" x-transition.scale.95 @click.away="openWifiModal = false" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md w-full">
                
                <div class="bg-indigo-600 p-4 sm:p-5 text-white flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-base sm:text-lg">📶 Ganti Wi-Fi</h3>
                        <p class="text-indigo-200 text-xs mt-0.5 line-clamp-1">{{ $device->customer ? $device->customer->name : 'Tanpa Nama' }}</p>
                    </div>
                    <button @click="openWifiModal = false" class="text-white/70 hover:text-white text-2xl leading-none bg-white/10 hover:bg-white/20 rounded-full w-8 h-8 flex items-center justify-center transition">&times;</button>
                </div>

                <div class="p-5 sm:p-6">
                    <form action="{{ route('helpdesk.update-wifi') }}" method="POST">
                        @csrf
                        <input type="hidden" name="genieacs_id" value="{{ $device->genieacs_id }}">
                        
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">Nama SSID Baru</label>
                            <input type="text" name="ssid" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                        </div>
                        <div class="mb-5">
                            <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">Password Baru <span class="font-normal text-slate-400 text-xs">(min. 8)</span></label>
                            <input type="text" name="password" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" required minlength="8">
                        </div>
                        <div class="flex flex-col sm:flex-row-reverse gap-2 sm:gap-3 mt-5">
                            <button type="submit" class="w-full sm:w-auto justify-center rounded-lg bg-indigo-600 px-4 py-2.5 sm:py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 transition">Terapkan Modem</button>
                            <button type="button" @click="openWifiModal = false" class="w-full sm:w-auto justify-center rounded-lg bg-white px-4 py-2.5 sm:py-2 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
