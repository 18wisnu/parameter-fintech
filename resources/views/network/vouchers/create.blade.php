@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Buat Voucher',
    'subtitle' => 'Generate voucher hotspot sekaligus (maks. 500 per batch). Hasilnya bisa langsung dicetak.',
    'actions' => [['← Daftar Voucher', route('network.vouchers.index')]],
])

@php $input = 'w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-100'; $label = 'block text-[10px] font-bold text-slate-400 mb-1.5'; @endphp

<form action="{{ route('network.vouchers.store') }}" method="POST" class="max-w-3xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5"
      onsubmit="this.querySelector('[type=submit]').disabled=true; this.querySelector('[type=submit]').innerText='Membuat voucher…';">
    @csrf

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
            <label class="{{ $label }}">Paket Hotspot</label>
            <select name="product_id" required class="{{ $input }}">
                <option value="">— Pilih paket —</option>
                @foreach($products as $p)
                <option value="{{ $p['id'] }}" @selected(old('product_id') == $p['id'])>
                    {{ $p['name'] }} — Rp {{ number_format($p['price'] ?? 0, 0, ',', '.') }}{{ !empty($p['bandwidth']) ? ' · ' . $p['bandwidth'] : '' }}
                </option>
                @endforeach
            </select>
            @if(empty($products))<p class="mt-1 text-xs text-amber-600">Belum ada paket hotspot aktif di server jaringan.</p>@endif
        </div>
        <div>
            <label class="{{ $label }}">Jumlah</label>
            <input type="number" name="quantity" min="1" max="500" value="{{ old('quantity', 10) }}" required class="{{ $input }}">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="{{ $label }}">Cara Login</label>
            <select name="login_method" id="login_method" class="{{ $input }}">
                <option value="voucher_code" @selected(old('login_method', 'voucher_code') === 'voucher_code')>Kode saja (password = kode)</option>
                <option value="user_pass" @selected(old('login_method') === 'user_pass')>Username + password terpisah</option>
            </select>
        </div>
        <div>
            <label class="{{ $label }}">Area Login</label>
            <select name="restriction_id" class="{{ $input }}">
                @forelse($restrictions as $r)
                <option value="{{ $r['id'] }}" @selected((string) old('restriction_id', '0') === (string) $r['id'])>{{ $r['name'] }}{{ !empty($r['is_global']) ? ' (semua lokasi)' : '' }}</option>
                @empty
                <option value="0">Global (semua lokasi)</option>
                @endforelse
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <label class="{{ $label }}">Awalan Kode</label>
            <input type="text" name="prefix" maxlength="10" value="{{ old('prefix') }}" placeholder="mis. VC" class="{{ $input }} uppercase">
        </div>
        <div>
            <label class="{{ $label }}">Panjang Kode</label>
            <input type="number" name="code_length" min="5" max="16" value="{{ old('code_length', 8) }}" class="{{ $input }}">
        </div>
        <div id="password_length_wrap">
            <label class="{{ $label }}">Panjang Password</label>
            <input type="number" name="password_length" min="5" max="16" value="{{ old('password_length', 6) }}" class="{{ $input }}">
        </div>
        <div>
            <label class="{{ $label }}">Karakter</label>
            <select name="code_combination" class="{{ $input }}">
                @foreach(['upper_num' => 'HURUF + angka', 'num_only' => 'Angka saja', 'lower_num' => 'huruf + angka', 'upper_only' => 'HURUF saja', 'lower_only' => 'huruf saja', 'upper_lower' => 'Huruf Besar+kecil', 'upper_lower_num' => 'Campuran'] as $k => $v)
                <option value="{{ $k }}" @selected(old('code_combination', 'upper_num') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <label class="flex items-center gap-3">
        <input type="checkbox" name="mac_binding" value="1" @checked(old('mac_binding')) class="rounded border-slate-300 text-sky-600">
        <span class="text-sm font-bold text-slate-700">Kunci ke perangkat pertama yang login</span>
    </label>

    <div>
        <label class="{{ $label }}">Catatan (opsional)</label>
        <input type="text" name="notes" maxlength="200" value="{{ old('notes') }}" placeholder="mis. Stok outlet Warung Bu Siti" class="{{ $input }}">
    </div>

    <div class="pt-2 flex flex-col sm:flex-row gap-3">
        <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md shadow-sky-100">Buat Voucher</button>
        <a href="{{ route('network.vouchers.index') }}" class="text-center bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-3 rounded-xl text-xs font-bold">Batal</a>
    </div>
</form>

<script>
    (function () {
        const method = document.getElementById('login_method');
        const wrap = document.getElementById('password_length_wrap');
        const sync = () => wrap.style.display = method.value === 'user_pass' ? '' : 'none';
        method.addEventListener('change', sync); sync();
    })();
</script>
@endsection
