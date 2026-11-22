@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Ajukan Pemasangan',
    'subtitle' => 'Daftarkan calon pelanggan baru. Pelanggan & layanan dibuat saat instalasi diaktifkan.',
    'actions' => [['← Daftar Pemasangan', route('network.installations.index')]],
])

@php $input = 'w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100'; $label = 'block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5'; @endphp

<form action="{{ route('network.installations.store') }}" method="POST" class="max-w-3xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="{{ $label }}">Nama Lengkap</label>
            <input type="text" name="fullname" required minlength="3" maxlength="100" value="{{ old('fullname') }}" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Jenis Layanan</label>
            <select name="service_type" id="service_type" required class="{{ $input }}">
                @foreach($types as $k => $v)<option value="{{ $k }}" @selected(old('service_type', 'ppp') === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Paket</label>
            <select name="product_id" id="product_id" required class="{{ $input }}">
                <option value="">— Pilih paket —</option>
                @foreach($products as $p)
                <option value="{{ $p['id'] }}" data-type="{{ $p['type'] ?? '' }}" @selected(old('product_id') == $p['id'])>{{ $p['name'] }} — Rp {{ number_format($p['price'] ?? 0, 0, ',', '.') }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Telepon / WhatsApp</label>
            <input type="text" name="phone" maxlength="20" value="{{ old('phone') }}" placeholder="628…" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Email (opsional)</label>
            <input type="email" name="email" value="{{ old('email') }}" class="{{ $input }}">
        </div>
        <div class="sm:col-span-2">
            <label class="{{ $label }}">Alamat Pemasangan</label>
            <textarea name="address" rows="2" maxlength="500" class="{{ $input }}">{{ old('address') }}</textarea>
        </div>
        <div>
            <label class="{{ $label }}">Latitude</label>
            <input type="text" name="latitude" id="latitude" value="{{ old('latitude') }}" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Longitude</label>
            <input type="text" name="longitude" id="longitude" value="{{ old('longitude') }}" class="{{ $input }}">
        </div>
        <div class="sm:col-span-2 -mt-2">
            <button type="button" id="geo" class="text-sky-600 text-xs font-bold hover:underline">Pakai lokasi saya sekarang</button>
        </div>
        <div>
            <label class="{{ $label }}">No. KTP / NIK (opsional)</label>
            <input type="text" name="id_number" maxlength="30" value="{{ old('id_number') }}" class="{{ $input }}">
        </div>
        <div id="nas_wrap">
            <label class="{{ $label }}">Tipe Koneksi PPP</label>
            <select name="nas_port_type" class="{{ $input }}">
                <option value="Ethernet" @selected(old('nas_port_type', 'Ethernet') === 'Ethernet')>PPPoE (Ethernet)</option>
                <option value="Virtual" @selected(old('nas_port_type') === 'Virtual')>Virtual</option>
                <option value="Async" @selected(old('nas_port_type') === 'Async')>Async</option>
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="{{ $label }}">Catatan</label>
            <input type="text" name="notes" maxlength="1000" value="{{ old('notes') }}" placeholder="mis. Pasang Sabtu pagi, rumah cat hijau" class="{{ $input }}">
        </div>
    </div>
    <button class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest">Simpan Pengajuan</button>
</form>

<script>
    (function () {
        const type = document.getElementById('service_type');
        const product = document.getElementById('product_id');
        const nas = document.getElementById('nas_wrap');
        const sync = () => {
            nas.style.display = type.value === 'ppp' ? '' : 'none';
            [...product.options].forEach(o => { if (o.value) o.hidden = o.dataset.type && o.dataset.type !== type.value; });
            if (product.selectedOptions[0]?.hidden) product.value = '';
        };
        type.addEventListener('change', sync); sync();
        document.getElementById('geo').addEventListener('click', () => {
            navigator.geolocation?.getCurrentPosition(p => {
                document.getElementById('latitude').value = p.coords.latitude.toFixed(6);
                document.getElementById('longitude').value = p.coords.longitude.toFixed(6);
            });
        });
    })();
</script>
@endsection
