@extends('layouts.admin')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Reset data & hapus usaha</h2>
        <p class="text-sm text-slate-500 mt-1">Khusus pemilik. Tindakan di halaman ini menghapus data <b>{{ $business->name }}</b> dan tidak bisa dibatalkan, kecuali hapus usaha selama masa tunggu {{ $graceDays }} hari.</p>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl text-sm space-y-1">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    {{-- RESET DATA --}}
    <form action="{{ route('admin.danger.reset') }}" method="POST"
          class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6"
          x-data="{ picked: @js(old('categories', [])), requires: @js(collect($categories)->map(fn ($c) => $c['requires'])) }"
          x-effect="picked.forEach(k => (requires[k] || []).forEach(r => { if (!picked.includes(r)) picked.push(r) }))"
          onsubmit="return confirm('Hapus data yang dicentang? Data yang sudah dihapus tidak bisa dikembalikan.')">
        @csrf
        <h3 class="font-bold text-slate-800">Reset data</h3>
        <p class="text-sm text-slate-500 mt-1">Pilih data yang ingin dikosongkan. Usaha, pengaturan, pegawai, dan langganan NetBill tetap ada. Data di server jaringan tidak ikut terhapus.</p>

        <div class="mt-4 space-y-2.5">
            @foreach($categories as $key => $cat)
            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer has-[:checked]:border-rose-300 has-[:checked]:bg-rose-50/50">
                <input type="checkbox" name="categories[]" value="{{ $key }}" x-model="picked"
                       class="mt-0.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-slate-800">{{ $cat['label'] }} <span class="font-normal text-slate-400">· {{ number_format($counts[$key], 0, ',', '.') }} data</span></span>
                    <span class="block text-xs text-slate-500 mt-0.5">{{ $cat['description'] }}</span>
                </span>
            </label>
            @endforeach
        </div>

        <div class="mt-5">
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ketik <b>{{ $business->name }}</b> untuk konfirmasi</label>
            <input type="text" name="confirm_name" autocomplete="off" required
                   class="w-full sm:max-w-sm px-3 py-2 rounded-xl border-slate-200 text-sm focus:border-rose-400 focus:ring-rose-400/30">
        </div>

        <button type="submit" :disabled="picked.length === 0"
                class="mt-4 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold">
            Hapus data yang dicentang
        </button>
    </form>

    {{-- HAPUS USAHA --}}
    <form action="{{ route('admin.danger.destroy') }}" method="POST"
          class="bg-white rounded-2xl shadow-sm border border-rose-200 p-6"
          onsubmit="return confirm('Kunci dan jadwalkan penghapusan usaha {{ addslashes($business->name) }}?')">
        @csrf @method('DELETE')
        <h3 class="font-bold text-rose-700">Hapus usaha</h3>
        <ul class="text-sm text-slate-600 mt-2 space-y-1 list-disc pl-5">
            <li>Usaha langsung <b>dikunci</b>: semua pegawai tidak bisa membukanya lagi.</li>
            <li>Selama <b>{{ $graceDays }} hari</b> Anda masih bisa membatalkan dari halaman <i>Pilih usaha</i>.</li>
            <li>Setelah itu, usaha beserta seluruh datanya <b>dihapus permanen</b>. Akun pegawai tetap ada untuk usaha lain.</li>
        </ul>

        <div class="mt-5">
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ketik <b>{{ $business->name }}</b> untuk konfirmasi</label>
            <input type="text" name="confirm_name" autocomplete="off" required
                   class="w-full sm:max-w-sm px-3 py-2 rounded-xl border-slate-200 text-sm focus:border-rose-400 focus:ring-rose-400/30">
        </div>

        <button type="submit" class="mt-4 px-5 py-2.5 rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-600 hover:text-white text-sm font-semibold">
            Hapus usaha ini
        </button>
    </form>
</div>
@endsection
