@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Tambah Pelanggan Baru</h2>
        <p class="text-slate-500 text-sm">Masukkan data pelanggan atau reseller baru.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            
            <div class="mb-5">
                <label class="block text-sm font-bold text-slate-700 mb-2">ID Pelanggan (Khusus PPPoE)</label>
                <input type="text" name="customer_id" class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-sky-500 font-bold" placeholder="Contoh: 100234">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-sky-500" placeholder="Nama Pelanggan">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tipe Layanan</label>
                    <select name="type" class="w-full px-4 py-3 rounded-xl border-slate-200 bg-white">
                        <option value="pppoe">Pelanggan Rumahan (PPPoE)</option>
                        <option value="hotspot">Voucheran (Hotspot)</option>
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Harga Paket Bulanan (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400 font-bold">Rp</span>
                        <input type="number" name="package_fee" required class="w-full pl-12 pr-4 py-3 rounded-xl border-slate-200 focus:border-sky-500 font-bold text-lg" placeholder="0">
                    </div>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Jatuh Tempo</label>
                    <input type="date" name="due_date" class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-sky-500 font-medium text-slate-600">
                    <p class="text-xs text-slate-400 mt-1">*Pelanggan akan di-ISOLIR otomatis jika belum bayar lewat tanggal ini.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">No. WhatsApp</label>
                    <input type="number" name="phone" class="w-full px-4 py-3 rounded-xl border-slate-200" placeholder="08xxxxx">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Diaktifkan Oleh (Teknisi)</label>
                    <select name="activated_by_id" class="w-full px-4 py-3 rounded-xl border-slate-200 bg-white">
                        <option value="">-- Tanpa Bonus --</option>
                        @foreach($staff as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ ucfirst($s->role) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">Alamat Pemasangan</label>
                <textarea name="address" rows="3" class="w-full px-4 py-3 rounded-xl border-slate-200" placeholder="Alamat lengkap..."></textarea>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('customers.index') }}" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50">Batal</a>
                <button type="submit" class="flex-1 bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-sky-200 transition">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection