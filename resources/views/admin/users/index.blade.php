@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Manajemen Pegawai</h2>
            <p class="text-xs text-slate-500 mt-1">Kelola data seluruh akun staf, teknisi, kolektor, dan hak akses.</p>
        </div>
        <div>
            <button onclick="document.getElementById('modalTambahPegawai').classList.remove('hidden')" 
                class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest shadow-md shadow-sky-100 transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Pegawai
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl flex items-center gap-3 text-xs font-bold">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl flex items-center gap-3 text-xs font-bold">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl text-xs space-y-1">
        @foreach($errors->all() as $error)
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    </div>
    @endif
    
    <!-- Desktop View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-sm">
                    <th class="px-4 py-3 font-black uppercase tracking-widest text-[10px]">Nama Pegawai</th>
                    <th class="px-4 py-3 font-black uppercase tracking-widest text-[10px]">Email</th>
                    <th class="px-4 py-3 font-black uppercase tracking-widest text-[10px]">Jabatan Saat Ini</th>
                    <th class="px-4 py-3 font-black uppercase tracking-widest text-[10px] text-right">Gaji Pokok</th>
                    <th class="px-4 py-3 font-black uppercase tracking-widest text-[10px] text-center">Aksi & Jabatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                    <td class="px-4 py-4 font-bold text-slate-700">
                        <div class="flex items-center gap-3">
                            @if($user->isOnline())
                                <div class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </div>
                                <span class="text-[9px] text-emerald-600 font-black uppercase tracking-widest bg-emerald-50 px-2 py-0.5 rounded">Online</span>
                            @else
                                <span class="w-2 h-2 bg-slate-300 rounded-full" title="Offline"></span>
                            @endif
                            <span class="text-sm">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-4 text-slate-500 text-sm font-medium">{{ $user->email }}</td>
                    <td class="px-4 py-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest
                            {{ $user->role == 'owner' ? 'bg-purple-100 text-purple-600' : 'bg-sky-100 text-sky-600' }}">
                            {{ $user->role ?? 'Staff' }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-right font-bold text-slate-700 text-sm">
                        Rp {{ number_format($user->base_salary ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <!-- Quick Role Change -->
                            <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST" class="flex items-center gap-1.5">
                                @csrf
                                @method('PUT')
                                <select name="role" class="text-xs border-slate-200 rounded-xl focus:ring-sky-500 focus:border-sky-500 py-1.5 px-2 bg-slate-50">
                                    <option value="owner" {{ $user->role == 'owner' ? 'selected' : '' }}>Owner</option>
                                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="teknisi" {{ $user->role == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                                    <option value="kolektor" {{ $user->role == 'kolektor' ? 'selected' : '' }}>Kolektor</option>
                                </select>
                                <button type="submit" class="bg-slate-900 text-white px-2.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-sky-600 transition-colors shadow-sm">Save</button>
                            </form>

                            <!-- Edit Detail Button -->
                            <button onclick="openEditModal({{ json_encode(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'base_salary' => $user->base_salary ?? 0]) }})" 
                                class="p-1.5 text-sky-600 hover:bg-sky-50 rounded-xl transition" title="Edit Profil & Password">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>

                            <!-- Delete Button -->
                            @if(auth()->id() !== $user->id)
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus pegawai {{ $user->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-xl transition" title="Hapus Pegawai">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile View (Vertical Cards) -->
    <div class="md:hidden grid grid-cols-1 gap-4">
        @foreach($users as $user)
            <div class="bg-slate-50 rounded-[2rem] p-6 border border-slate-100 relative overflow-hidden">
                @if($user->isOnline())
                    <div class="absolute top-0 right-0 px-4 py-1 bg-emerald-500 text-white text-[9px] font-black uppercase tracking-widest rounded-bl-2xl">Online</div>
                @endif
                
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center font-black text-slate-800 text-lg uppercase border border-slate-100">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-black text-slate-800 text-base leading-tight">{{ $user->name }}</h4>
                        <p class="text-[10px] text-slate-400 font-bold lowercase mt-0.5 tracking-tight">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between py-2 border-y border-slate-200/40 text-xs">
                    <span class="text-slate-400 font-bold">Gaji Pokok:</span>
                    <span class="font-black text-slate-800">Rp {{ number_format($user->base_salary ?? 0, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between mt-4">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Jabatan Sekarang</p>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-white border border-slate-100 text-slate-600">
                            {{ $user->role ?? 'Staff' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <button onclick="openEditModal({{ json_encode(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'base_salary' => $user->base_salary ?? 0]) }})" 
                            class="bg-white border border-slate-200 text-sky-600 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm active:scale-95 transition-all">
                            Edit
                        </button>
                        <button onclick="document.getElementById('mobile-role-{{ $user->id }}').classList.toggle('hidden')" class="bg-white border border-slate-200 text-slate-600 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm active:scale-95 transition-all">
                            Role
                        </button>
                        @if(auth()->id() !== $user->id)
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus pegawai {{ $user->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-rose-50 border border-rose-200 text-rose-600 p-2 rounded-xl shadow-sm active:scale-95 transition-all" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <!-- Hidden form for mobile -->
                <div id="mobile-role-{{ $user->id }}" class="hidden mt-6 bg-white p-5 rounded-2xl border border-slate-100 animate-in fade-in slide-in-from-top-2 duration-300">
                    <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Pilih Jabatan Baru</label>
                        <select name="role" class="w-full text-sm border-slate-200 rounded-xl focus:ring-sky-500 mb-4 bg-slate-50">
                            <option value="owner" {{ $user->role == 'owner' ? 'selected' : '' }}>Owner</option>
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="teknisi" {{ $user->role == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                            <option value="kolektor" {{ $user->role == 'kolektor' ? 'selected' : '' }}>Kolektor</option>
                        </select>
                        <button type="submit" class="w-full bg-sky-600 text-white font-black py-3 rounded-xl shadow-lg shadow-sky-100 text-[10px] uppercase tracking-widest">Update Jabatan</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal Tambah Pegawai -->
<div id="modalTambahPegawai" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-[2rem] max-w-lg w-full p-8 shadow-2xl border border-slate-100 relative animate-in fade-in zoom-in duration-200">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-black text-slate-800">Tambah Pegawai Baru</h3>
                <p class="text-xs text-slate-400 mt-0.5">Buat akun untuk staf atau teknisi lapangan.</p>
            </div>
            <button onclick="document.getElementById('modalTambahPegawai').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" required placeholder="Contoh: Budi Santoso"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Email Akun</label>
                <input type="email" name="email" required placeholder="budi@example.com"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" minlength="6"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Jabatan / Role</label>
                    <select name="role" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
                        <option value="teknisi">Teknisi</option>
                        <option value="kolektor">Kolektor</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Gaji Pokok (Rp)</label>
                    <input type="number" name="base_salary" min="0" placeholder="0"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="document.getElementById('modalTambahPegawai').classList.add('hidden')" 
                    class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 py-3.5 rounded-xl font-bold text-xs uppercase tracking-widest transition">
                    Batal
                </button>
                <button type="submit" 
                    class="flex-1 bg-sky-600 hover:bg-sky-700 text-white py-3.5 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-sky-200 transition active:scale-95">
                    Simpan Pegawai
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pegawai & Password -->
<div id="modalEditPegawai" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-[2rem] max-w-lg w-full p-8 shadow-2xl border border-slate-100 relative animate-in fade-in zoom-in duration-200">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-black text-slate-800">Edit Profil Pegawai</h3>
                <p class="text-xs text-slate-400 mt-0.5">Ubah data profil, jabatan, atau reset password.</p>
            </div>
            <button onclick="document.getElementById('modalEditPegawai').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="formEditPegawai" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                <input type="text" id="edit_name" name="name" required
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Email Akun</label>
                <input type="email" id="edit_email" name="email" required
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Password Baru <span class="text-slate-300 font-normal normal-case">(Kosongkan jika tidak diganti)</span></label>
                <input type="password" id="edit_password" name="password" placeholder="••••••••" minlength="6"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Jabatan / Role</label>
                    <select id="edit_role" name="role" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
                        <option value="teknisi">Teknisi</option>
                        <option value="kolektor">Kolektor</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Gaji Pokok (Rp)</label>
                    <input type="number" id="edit_base_salary" name="base_salary" min="0" placeholder="0"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="document.getElementById('modalEditPegawai').classList.add('hidden')" 
                    class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 py-3.5 rounded-xl font-bold text-xs uppercase tracking-widest transition">
                    Batal
                </button>
                <button type="submit" 
                    class="flex-1 bg-sky-600 hover:bg-sky-700 text-white py-3.5 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-sky-200 transition active:scale-95">
                    Perbarui Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(user) {
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_base_salary').value = user.base_salary;
        document.getElementById('edit_password').value = '';

        const form = document.getElementById('formEditPegawai');
        form.action = '/admin/users/' + user.id;

        document.getElementById('modalEditPegawai').classList.remove('hidden');
    }
</script>
@endsection