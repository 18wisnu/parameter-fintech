<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-bold text-2xl text-slate-800 leading-tight flex items-center gap-3">
                <div class="p-2 bg-amber-100 text-amber-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                Manajemen Titik ODP
            </h2>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest italic">Monitoring & Inventori ODP</div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ openCreateModal: false, isEditOpen: false, selectedOdp: {} }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Tombol Pindah ke Sini --}}
            <div class="flex justify-end mb-6">
                <button @click="openCreateModal = true" class="bg-slate-900 text-white px-8 py-3 rounded-2xl font-black shadow-xl hover:bg-slate-800 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center gap-2">
                    <div class="bg-white/20 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    Tambah ODP Baru
                </button>
            </div>
            
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Table / List --}}
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama ODP</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Parent / Distribusi</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Titik Koordinat</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($odps as $odp)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-6 font-bold text-slate-800">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black">
                                            <i class="fas fa-project-diagram"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm uppercase italic tracking-tight">{{ $odp->name }}</p>
                                            <p class="text-[10px] text-slate-400 font-normal">Kapasitas: {{ $odp->capacity }} Core</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 uppercase font-bold text-slate-800">
                                    @if($odp->parent)
                                        <div class="flex flex-col gap-1">
                                            <span class="text-[9px] text-amber-500 leading-none">Dari ODP: {{ $odp->parent->name }}</span>
                                            <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-amber-100 w-fit">
                                                Cascading
                                            </span>
                                        </div>
                                    @else
                                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-100 w-fit">
                                            {{ $odp->site->name ?? 'Headend' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="inline-flex flex-col items-center">
                                         <span class="text-[11px] font-mono text-slate-600">{{ $odp->latitude ?: '-' }}, {{ $odp->longitude ?: '-' }}</span>
                                         @if($odp->latitude)
                                         <a href="https://www.google.com/maps?q={{ $odp->latitude }},{{ $odp->longitude }}" target="_blank" class="text-[9px] text-blue-500 hover:underline font-bold mt-1">Lihat Google Maps</a>
                                         @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="selectedOdp = {{ json_encode($odp) }}; isEditOpen = true" class="p-2 text-slate-400 hover:text-amber-500 transition">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <form action="{{ route('helpdesk.odps.destroy', $odp->id) }}" method="POST" onsubmit="return confirm('Hapus ODP ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-slate-400 hover:text-rose-500 transition">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        </div>
                                        <p class="text-slate-400 font-bold italic">Belum ada titik ODP terdaftar.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Create Modal --}}
        <div x-show="openCreateModal" style="display:none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div x-show="openCreateModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div @click.away="openCreateModal = false" x-show="openCreateModal" x-transition.scale.95 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-full">
                <div class="bg-slate-900 p-6 text-white flex justify-between items-center">
                    <h3 class="font-black uppercase italic tracking-tight text-lg">Tambah ODP Baru</h3>
                    <button @click="openCreateModal = false" class="text-white/60 hover:text-white text-2xl leading-none">&times;</button>
                </div>
                <div class="p-8 overflow-y-auto">
                    <form action="{{ route('helpdesk.odps.store') }}" method="POST" class="space-y-5">
                        @csrf
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama ODP (Identitas)</label>
                                    <input type="text" name="name" class="w-full rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500 text-sm font-bold" placeholder="Contoh: ODP-TGP-01" required>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Kapasitas (Port)</label>
                                        <input type="number" name="capacity" class="w-full rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500 text-sm font-bold" placeholder="8" value="8" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipe Splitter</label>
                                        <select name="splitter_type" class="w-full rounded-xl border-slate-200 focus:ring-amber-500 text-sm font-bold">
                                            <option value="1:2">Splitter 1:2 (-3.5 dB)</option>
                                            <option value="1:4">Splitter 1:4 (-7.2 dB)</option>
                                            <option value="1:8" selected>Splitter 1:8 (-10.5 dB)</option>
                                            <option value="1:16">Splitter 1:16 (-13.8 dB)</option>
                                            <option value="1:32">Splitter 1:32 (-17.5 dB)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Parent OLT (Site)</label>
                                        <select name="site_id" class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 text-sm font-bold">
                                            <option value="">-- opsional --</option>
                                            @foreach($sites as $site)
                                                <option value="{{ $site->id }}">{{ $site->name }} ({{ $site->location }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Parent ODP (Cascading)</label>
                                        <select name="parent_odp_id" class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 text-sm font-bold">
                                            <option value="">-- Langsung dari OLT --</option>
                                            @foreach($odps as $parentOdp)
                                                <option value="{{ $parentOdp->id }}">{{ $parentOdp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Latitude</label>
                                <input type="text" name="latitude" class="w-full rounded-xl border-slate-200 text-sm font-mono" placeholder="-0.0xxxx">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Longitude</label>
                                <input type="text" name="longitude" class="w-full rounded-xl border-slate-200 text-sm font-mono" placeholder="109.xxxx">
                            </div>
                        </div>
                        <div class="pt-4 flex justify-end gap-3">
                            <button type="button" @click="openCreateModal = false" class="px-6 py-2.5 text-slate-500 font-bold hover:bg-slate-50 rounded-xl transition">Batal</button>
                            <button type="submit" class="bg-amber-500 text-white px-8 py-2.5 rounded-xl font-black shadow-lg shadow-amber-100 hover:bg-amber-600 transition-all">Simpan ODP</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="isEditOpen" style="display:none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div x-show="isEditOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div @click.away="isEditOpen = false" x-show="isEditOpen" x-transition.scale.95 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-full">
                <div class="bg-amber-600 p-6 text-white flex justify-between items-center">
                    <h3 class="font-black uppercase italic tracking-tight text-lg">Edit Data ODP</h3>
                    <button @click="isEditOpen = false" class="text-white/60 hover:text-white text-2xl leading-none">&times;</button>
                </div>
                <div class="p-8 overflow-y-auto">
                    <form :action="`/odps/update/${selectedOdp.id}`" method="POST" class="space-y-5">
                        @csrf @method('PUT')
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama ODP</label>
                                <input type="text" name="name" x-model="selectedOdp.name" class="w-full rounded-xl border-slate-200 focus:ring-amber-500 text-sm font-bold" required>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Kapasitas</label>
                                    <input type="number" name="capacity" x-model="selectedOdp.capacity" class="w-full rounded-xl border-slate-200 focus:ring-amber-500 text-sm font-bold" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipe Splitter</label>
                                    <select name="splitter_type" x-model="selectedOdp.splitter_type" class="w-full rounded-xl border-slate-200 focus:ring-amber-500 text-sm font-bold">
                                        <option value="1:2">Splitter 1:2 (-3.5 dB)</option>
                                        <option value="1:4">Splitter 1:4 (-7.2 dB)</option>
                                        <option value="1:8">Splitter 1:8 (-10.5 dB)</option>
                                        <option value="1:16">Splitter 1:16 (-13.8 dB)</option>
                                        <option value="1:32">Splitter 1:32 (-17.5 dB)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Parent OLT</label>
                                    <select name="site_id" x-model="selectedOdp.site_id" class="w-full rounded-xl border-slate-200 text-sm font-bold">
                                        <option value="">-- opsional --</option>
                                        @foreach($sites as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Parent ODP</label>
                                    <select name="parent_odp_id" x-model="selectedOdp.parent_odp_id" class="w-full rounded-xl border-slate-200 text-sm font-bold">
                                        <option value="">-- Langsung dari OLT --</option>
                                        @foreach($odps as $parentOdp)
                                            <option :value="{{ $parentOdp->id }}" x-show="selectedOdp.id != {{ $parentOdp->id }}">{{ $parentOdp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Latitude</label>
                                <input type="text" name="latitude" x-model="selectedOdp.latitude" class="w-full rounded-xl border-slate-200 text-sm font-mono">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Longitude</label>
                                <input type="text" name="longitude" x-model="selectedOdp.longitude" class="w-full rounded-xl border-slate-200 text-sm font-mono">
                            </div>
                        </div>
                        <div class="pt-4 flex justify-end gap-3">
                            <button type="button" @click="isEditOpen = false" class="px-6 py-2.5 text-slate-500 font-bold hover:bg-slate-50 rounded-xl transition">Batal</button>
                            <button type="submit" class="bg-slate-900 text-white px-8 py-2.5 rounded-xl font-black shadow-lg hover:bg-slate-800 transition-all">Update Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('odp', {
                createOpen: false,
                editOpen: false,
                selected: {},
            });
        });

        // Helper global functions to interact with store
        window.openCreateModal = () => {
            Alpine.store('odp').createOpen = true;
        }
        window.openEditModal = (odp) => {
            Alpine.store('odp').selected = odp;
            Alpine.store('odp').editOpen = true;
        }
    </script>
    @endpush
</x-app-layout>
