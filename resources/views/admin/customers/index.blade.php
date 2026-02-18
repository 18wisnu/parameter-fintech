@extends('layouts.admin')

@section('content')

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Data Pelanggan</h2>
            <p class="text-slate-500 text-sm">Kelola daftar pelanggan, status isolir, dan export data.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('customers.export') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl shadow-md font-bold flex items-center transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </a>

            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2.5 rounded-xl shadow-md font-bold flex items-center transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Import Excel
            </button>

            <a href="{{ route('customers.create') }}" class="bg-blue-600 text-white px-4 py-2.5 rounded-xl hover:bg-blue-700 shadow-md font-bold flex items-center transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Baru
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-semibold w-32">ID / KODE</th>
                        <th class="px-6 py-4 font-semibold">NAMA PELANGGAN</th>
                        <th class="px-6 py-4 font-semibold text-center">STATUS</th> <th class="px-6 py-4 font-semibold">TIPE</th>
                        <th class="px-6 py-4 font-semibold">KONTAK</th>
                        <th class="px-6 py-4 font-semibold">ALAMAT</th>
                        <th class="px-6 py-4 font-semibold text-right">OPSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($customers as $customer)
                    <tr class="hover:bg-slate-50 transition-colors">
                        
                        <td class="px-6 py-4">
                            @if(!empty($customer->customer_id))
                                <span class="font-mono text-xs font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded border border-slate-200">
                                    #{{ $customer->customer_id }}
                                </span>
                            @else
                                <span class="text-slate-300 font-bold">-</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 font-bold text-slate-700">{{ $customer->name }}</td>

                        <td class="px-6 py-4 text-center">
                            @if($customer->is_isolated)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600 mr-1.5"></span>
                                    ISOLIR
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mr-1.5"></span>
                                    AKTIF
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4">
                            @if($customer->type == 'reseller')
                                <span class="text-xs font-bold text-purple-600 uppercase">Reseller</span>
                            @elseif($customer->type == 'hotspot')
                                <span class="text-xs font-bold text-orange-600 uppercase">Hotspot</span>
                            @else
                                <span class="text-xs font-bold text-blue-600 uppercase">PPPoE</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-slate-600">{{ $customer->phone ?? '-' }}</td>

                        <td class="px-6 py-4 text-slate-500 text-sm truncate max-w-xs">{{ $customer->address ?? '-' }}</td>
                        
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('customers.edit', $customer->id) }}" class="text-sm font-bold text-sky-600 hover:text-sky-800">Edit</a>
                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-bold text-rose-500 hover:text-rose-700" onclick="return confirm('Hapus pelanggan ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($customers->isEmpty())
            <div class="p-8 text-center text-slate-400">
                Belum ada data pelanggan. Silakan Import atau Tambah Baru.
            </div>
        @endif
        
        <div class="p-4">
            {{ $customers->links() }}
        </div>
    </div>

    <div id="importModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4 transition-opacity">
        <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl relative">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800">Import Data Pelanggan</h3>
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <p class="text-sm text-slate-500 mb-4 bg-slate-50 p-3 rounded-lg border border-slate-100">
                Upload file Excel <b>(.xlsx)</b>. <br>
                Header: <code class="text-rose-500 font-bold">nama, tipe, hp, alamat, id_pelanggan</code>
            </p>
            <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File</label>
                    <input type="file" name="file" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 border border-slate-200 rounded-xl cursor-pointer"/>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-sky-600 text-white font-bold rounded-xl hover:bg-sky-700 shadow-lg shadow-sky-200 transition">Upload & Proses</button>
                </div>
            </form>
        </div>
    </div>

@endsection