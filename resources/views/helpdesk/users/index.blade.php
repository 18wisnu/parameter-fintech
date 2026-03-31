<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight italic uppercase tracking-widest">
            {{ __('Manajemen Admin & Staff') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Alert --}}
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-2xl flex items-center shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl flex items-center shadow-sm" role="alert">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Form Add New User --}}
                <div class="lg:col-span-1">
                    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 sticky top-24">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-600 rounded-xl shadow-lg shadow-indigo-100">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            </div>
                            <h3 class="text-lg font-black text-slate-800 uppercase italic tracking-tight">Daftarkan Admin</h3>
                        </div>

                        <form action="{{ route('helpdesk.users.store') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1.5 ml-1">Nama Lengkap</label>
                                    <input type="text" name="name" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold" placeholder="Misal: Agus Admin" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1.5 ml-1">Email Karyawan</label>
                                    <input type="email" name="email" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold" placeholder="Misal: agus@tanjungpura.net" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1.5 ml-1">Nomor HP/WA</label>
                                    <input type="text" name="phone" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold" placeholder="Misal: 08123xxx">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1.5 ml-1">Role Jabatan</label>
                                        <select name="role" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold">
                                            <option value="admin">Admin</option>
                                            <option value="staff">Staff Teknis</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1.5 ml-1">Penempatan Site</label>
                                        <select name="site_id" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold">
                                            <option value="">Semua Site</option>
                                            @foreach($sites as $site)
                                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1.5 ml-1">Kata Sandi</label>
                                        <input type="password" name="password" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1.5 ml-1">Konfirmasi</label>
                                        <input type="password" name="password_confirmation" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold" required>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="mt-8 w-full bg-indigo-600 hover:bg-slate-900 text-white font-black py-3 px-4 rounded-xl shadow-lg shadow-indigo-100 transition-all duration-300 uppercase tracking-widest text-xs italic">
                                Simpan Admin Baru
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Table Of Users --}}
                <div class="lg:col-span-2">
                    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h3 class="font-black text-slate-800 uppercase italic tracking-tight">Daftar Admin Aktif</h3>
                            <span class="px-3 py-1 bg-white border border-slate-200 rounded-full text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ $users->count() }} Orang</span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4">Nama & Email</th>
                                        <th class="px-6 py-4">Jabatan</th>
                                        <th class="px-6 py-4">Site OLT</th>
                                        <th class="px-6 py-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($users as $user)
                                        <tr class="hover:bg-slate-50/50 transition-colors" x-data="{ editing: false }">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center font-black text-indigo-600 border border-indigo-100 uppercase italic">
                                                        {{ substr($user->name, 0, 2) }}
                                                    </div>
                                                    <div>
                                                        <div x-show="!editing" class="font-black text-slate-800 text-sm italic">{{ $user->name }}</div>
                                                        <input x-show="editing" form="form-edit-{{ $user->id }}" type="text" name="name" value="{{ $user->name }}" class="text-sm font-bold p-1 rounded-lg border-slate-200 w-full">
                                                        
                                                        <div x-show="!editing" class="text-[11px] font-bold text-slate-400">{{ $user->email }}</div>
                                                        <input x-show="editing" form="form-edit-{{ $user->id }}" type="email" name="email" value="{{ $user->email }}" class="text-xs font-bold p-1 rounded-lg border-slate-200 w-full mt-1">
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <template x-if="!editing">
                                                    <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $user->role === 'admin' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                                                        {{ $user->role }}
                                                    </span>
                                                </template>
                                                <select x-show="editing" form="form-edit-{{ $user->id }}" name="role" class="text-xs font-bold p-1 rounded-lg border-slate-200">
                                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>Staff</option>
                                                </select>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div x-show="!editing" class="text-xs font-bold text-slate-600">
                                                    {{ $user->site->name ?? '-' }}
                                                </div>
                                                <select x-show="editing" form="form-edit-{{ $user->id }}" name="site_id" class="text-xs font-bold p-1 rounded-lg border-slate-200">
                                                    <option value="">Semua Site</option>
                                                    @foreach($sites as $site)
                                                        <option value="{{ $site->id }}" {{ $user->site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex justify-center items-center gap-2">
                                                    <form id="form-edit-{{ $user->id }}" action="{{ route('helpdesk.users.update', $user->id) }}" method="POST" class="hidden">
                                                        @csrf
                                                        @method('PUT')
                                                    </form>
                                                    
                                                    @if(auth()->id() !== $user->id)
                                                        <button x-show="!editing" @click="editing = true" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        </button>
                                                        
                                                        <button x-show="editing" form="form-edit-{{ $user->id }}" type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-xl transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        </button>
                                                        
                                                        <form action="{{ route('helpdesk.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus admin ini? Pekerjaan admin ini tidak akan hilang, tapi dia tidak bisa login lagi.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" x-show="!editing" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-[9px] font-black uppercase text-indigo-400 tracking-widest px-2 py-1 bg-indigo-50 rounded-lg italic">Anda (Aktif)</span>
                                                    @endif
                                                    
                                                    <button x-show="editing" @click="editing = false" type="button" class="p-2 text-slate-400 hover:bg-slate-100 rounded-xl transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
