@extends('layouts.admin')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h2 class="text-xl font-bold text-slate-800">Integrasi Mixradius Evo</h2>
        <p class="text-xs text-slate-500 mt-1">Koneksi API ke panel Mixradius Evo untuk unit bisnis <span class="font-bold text-slate-700">{{ $business->name }}</span>. Setiap unit bisnis punya koneksinya sendiri.</p>

        @if(session('success'))
        <div class="mt-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl text-xs font-bold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="mt-6 p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl text-xs font-bold break-words">{{ session('error') }}</div>
        @endif
        @if($errors->any())
        <div class="mt-6 p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl text-xs font-bold">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
        @endif

        <form action="{{ route('admin.mixradius.update') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <label class="flex items-center gap-3">
                <input type="hidden" name="mixradius_enabled" value="0">
                <input type="checkbox" name="mixradius_enabled" value="1" {{ old('mixradius_enabled', $business->mixradius_enabled) ? 'checked' : '' }}
                    class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                <span class="text-sm font-bold text-slate-700">Aktifkan integrasi Mixradius</span>
            </label>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">URL Panel Mixradius</label>
                <input type="url" name="mixradius_base_url" value="{{ old('mixradius_base_url', $business->mixradius_base_url) }}" placeholder="https://billing.domainanda.com"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">API Key</label>
                    <input type="password" name="mixradius_api_key" autocomplete="off" placeholder="{{ $hasKey ? '•••••••• (tersimpan, kosongkan jika tidak diganti)' : 'Masukkan API key' }}"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">API Secret</label>
                    <input type="password" name="mixradius_api_secret" autocomplete="off" placeholder="{{ $hasSecret ? '•••••••• (tersimpan, kosongkan jika tidak diganti)' : 'Masukkan API secret' }}"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all">
                </div>
            </div>
            <p class="text-xs text-slate-400">API key &amp; secret disimpan terenkripsi dan tidak pernah ditampilkan kembali.</p>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-3 rounded-xl text-xs font-black uppercase tracking-widest shadow-md shadow-sky-100 transition active:scale-95">
                    Simpan Pengaturan
                </button>
            </div>
        </form>

        <div class="mt-3 flex flex-col sm:flex-row gap-3">
            <form action="{{ route('admin.mixradius.test') }}" method="POST">
                @csrf
                <button type="submit" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-700 text-white px-5 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition active:scale-95">
                    Tes Koneksi
                </button>
            </form>

            @if($business->hasMixradius())
            <form action="{{ route('admin.mixradius.sync') }}" method="POST" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerText='Menyinkronkan…';">
                @csrf
                <button type="submit" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition active:scale-95">
                    Sinkronkan Pelanggan Sekarang
                </button>
            </form>
            @endif
        </div>

        <p class="mt-4 text-xs text-slate-400">
            Sinkronisasi terakhir: {{ $business->mixradius_last_sync_at ? $business->mixradius_last_sync_at->format('d M Y H:i') : 'belum pernah' }}
            · otomatis tiap jam saat integrasi aktif.
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 text-sm text-slate-600 space-y-2">
        <h3 class="font-bold text-slate-800">Cara menghubungkan</h3>
        <ol class="list-decimal ml-5 space-y-1">
            <li>Di panel Mixradius buka <span class="font-bold">Settings → API Integration</span>, buat API Key untuk aplikasi ini.</li>
            <li>Beri izin minimal <code class="text-xs bg-slate-100 px-1 rounded">customers.read</code> dan <code class="text-xs bg-slate-100 px-1 rounded">services.read</code>.</li>
            <li>Isi URL panel (mis. <code class="text-xs bg-slate-100 px-1 rounded">https://namaanda.mixradius.com</code>), API key &amp; secret di atas, centang aktif, simpan, lalu Tes Koneksi.</li>
        </ol>
        <p class="text-xs text-slate-400">Data yang ditarik: nama, kode, telepon, alamat, status (aktif/isolir/nonaktif), jenis layanan, harga paket, jatuh tempo, dan username layanan. Tagihan, setoran &amp; petugas aktivasi lokal tidak diubah.</p>
    </div>
</div>
@endsection
