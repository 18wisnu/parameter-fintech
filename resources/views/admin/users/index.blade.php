@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <h2 class="text-xl font-bold text-slate-800 mb-6">Manajemen Pegawai</h2>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-sm">
                    <th class="px-4 py-3 font-medium">NAMA</th>
                    <th class="px-4 py-3 font-medium">EMAIL</th>
                    <th class="px-4 py-3 font-medium">JABATAN SAAT INI</th>
                    <th class="px-4 py-3 font-medium text-center">GANTI JABATAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                    <td class="px-4 py-4 font-bold text-slate-700">{{ $user->name }}</td>
                    <td class="px-4 py-4 text-slate-500 text-sm">{{ $user->email }}</td>
                    <td class="px-4 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase 
                            {{ $user->role == 'owner' ? 'bg-purple-100 text-purple-600' : 'bg-emerald-100 text-emerald-600' }}">
                            {{ $user->role ?? 'Belum Diatur' }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        {{-- PERBAIKAN DI SINI: --}}
                        <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST" class="flex gap-2">
                            @csrf
                            
                            {{-- INI KUNCINYA BOS! GANTI PATCH JADI PUT --}}
                            @method('PUT')

                            <select name="role" class="text-sm border-slate-200 rounded-lg focus:ring-emerald-500">
                                <option value="owner" {{ $user->role == 'owner' ? 'selected' : '' }}>Owner</option>
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="teknisi" {{ $user->role == 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                                <option value="kolektor" {{ $user->role == 'kolektor' ? 'selected' : '' }}>Kolektor</option>
                            </select>
                            
                            <button type="submit" class="bg-emerald-600 text-white px-3 py-1 rounded-lg text-xs font-bold hover:bg-emerald-700">Set</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection