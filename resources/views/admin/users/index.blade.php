@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <h2 class="text-xl font-bold text-slate-800 mb-6">Manajemen Pegawai</h2>
    
    <!-- Desktop View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-sm">
                    <th class="px-4 py-3 font-black uppercase tracking-widest text-[10px]">Nama Pegawai</th>
                    <th class="px-4 py-3 font-black uppercase tracking-widest text-[10px]">Email</th>
                    <th class="px-4 py-3 font-black uppercase tracking-widest text-[10px]">Jabatan Saat Ini</th>
                    <th class="px-4 py-3 font-black uppercase tracking-widest text-[10px] text-center">Ganti Jabatan (Aksi)</th>
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
                    <td class="px-4 py-4">
                        <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST" class="flex justify-center gap-2">
                            @csrf
                            @method('PUT')
                            <select name="role" class="text-xs border-slate-200 rounded-xl focus:ring-sky-500 focus:border-sky-500 py-1.5 px-3 bg-slate-50">
                                <option value="owner" {{ $user->role == 'owner' ? 'selected' : '' }}>Owner</option>
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="teknisi" {{ $user->role == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                                <option value="kolektor" {{ $user->role == 'kolektor' ? 'selected' : '' }}>Kolektor</option>
                            </select>
                            <button type="submit" class="bg-slate-900 text-white px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-sky-600 transition-colors shadow-sm">Save</button>
                        </form>
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

                <div class="flex items-center justify-between mt-6 pt-6 border-t border-slate-200/50">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Jabatan Sekarang</p>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-white border border-slate-100 text-slate-600">
                            {{ $user->role ?? 'Staff' }}
                        </span>
                    </div>
                    
                    <button onclick="document.getElementById('mobile-role-{{ $user->id }}').classList.toggle('hidden')" class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm active:scale-95 transition-all">
                        Ubah Role
                    </button>
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
                        <button type="submit" class="w-full bg-sky-600 text-white font-black py-3 rounded-xl shadow-lg shadow-sky-100 text-[10px] uppercase tracking-widest tracking-widest">Update Jabatan</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection