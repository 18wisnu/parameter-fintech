@extends('layouts.admin')

@section('content')
@include('partials.flash')
@php
    $sel = 'py-2 rounded-xl border-slate-200 text-sm text-slate-600';
    $eventTone = ['created' => 'bg-emerald-100 text-emerald-800', 'updated' => 'bg-sky-100 text-sky-800', 'deleted' => 'bg-rose-100 text-rose-800'];
    $eventLabel = ['created' => 'Tambah', 'updated' => 'Ubah', 'deleted' => 'Hapus'];
    // Nama kolom yang umum, sisanya tampil apa adanya
    $fields = [
        'amount' => 'Nominal', 'status' => 'Status', 'description' => 'Keterangan', 'notes' => 'Catatan', 'date' => 'Tanggal',
        'transaction_date' => 'Tanggal', 'account_id' => 'Akun', 'source_account_id' => 'Akun kas', 'payment_method' => 'Metode',
        'reference' => 'Referensi', 'type' => 'Jenis', 'name' => 'Nama', 'phone' => 'No. HP', 'address' => 'Alamat',
        'package_fee' => 'Harga paket', 'discount' => 'Diskon', 'installation_fee' => 'Biaya pasang', 'collector_id' => 'Kolektor',
        'due_date' => 'Jatuh tempo', 'is_isolated' => 'Isolir', 'void_reason' => 'Alasan void', 'invoice_number' => 'No. tagihan',
        'period_date' => 'Periode', 'user_id' => 'Petugas', 'customer_id' => 'Pelanggan', 'invoice_id' => 'Tagihan',
        'actual_amount' => 'Uang diterima', 'expected_amount' => 'Tercatat', 'difference' => 'Selisih', 'renewal_status' => 'Perpanjangan',
    ];
    $show = fn ($v) => $v === null || $v === '' ? '—' : \Illuminate\Support\Str::limit(is_bool($v) ? ($v ? 'ya' : 'tidak') : (string) $v, 80);
@endphp

<div class="mb-5">
    <h2 class="text-2xl font-bold text-slate-800">Jejak Audit</h2>
    <p class="text-sm text-slate-500">Siapa menambah, mengubah atau menghapus transaksi, tagihan, setoran, pengeluaran dan data pelanggan — dan apa yang berubah.</p>
</div>

<form method="GET" class="bg-white rounded-2xl border border-slate-100 p-3 mb-4 flex flex-wrap items-center gap-2">
    <select name="model_type" class="{{ $sel }}">
        <option value="">Semua data</option>
        @foreach($types as $k => $v)<option value="{{ $k }}" @selected(request('model_type') === $k)>{{ $v }}</option>@endforeach
    </select>
    <select name="event" class="{{ $sel }}">
        <option value="">Semua aksi</option>
        @foreach($eventLabel as $k => $v)<option value="{{ $k }}" @selected(request('event') === $k)>{{ $v }}</option>@endforeach
    </select>
    <select name="user_id" class="{{ $sel }}">
        <option value="">Semua pengguna</option>
        <option value="system" @selected(request('user_id') === 'system')>Sistem (otomatis)</option>
        @foreach($staff as $s)<option value="{{ $s->id }}" @selected(request('user_id') == $s->id)>{{ $s->name }}</option>@endforeach
    </select>
    <input type="date" name="start_date" value="{{ request('start_date') }}" class="{{ $sel }}">
    <span class="text-slate-400 text-sm">–</span>
    <input type="date" name="end_date" value="{{ request('end_date') }}" class="{{ $sel }}">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari no. tagihan / nama…" class="flex-1 min-w-[160px] py-2 px-3 rounded-xl border-slate-200 text-sm">
    <button class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">Terapkan</button>
</form>

<div class="bg-white rounded-2xl border border-slate-100 divide-y divide-slate-50">
    @forelse($logs as $log)
        @php $old = $log->payload['old'] ?? null; $new = $log->payload['new'] ?? null; @endphp
        <div class="px-5 py-3" x-data="{ open: false }">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                    <span class="inline-block text-[11px] font-bold px-2 py-0.5 rounded-full {{ $eventTone[$log->event] ?? 'bg-slate-100 text-slate-700' }}">{{ $eventLabel[$log->event] ?? $log->event }}</span>
                    <span class="text-sm text-slate-800">{{ $log->activity }}</span>
                    <div class="text-xs text-slate-400 mt-0.5">{{ $log->user->name ?? 'Sistem' }} · {{ $log->created_at->format('d/m/Y H:i:s') }}{{ $log->ip_address ? ' · ' . $log->ip_address : '' }}</div>
                </div>
                <button type="button" @click="open = !open" class="text-xs font-semibold text-sky-600 hover:underline" x-text="open ? 'Tutup' : 'Rincian'"></button>
            </div>

            <div x-show="open" x-cloak class="mt-2 overflow-x-auto">
                <table class="text-xs w-full">
                    @if($log->event === 'updated')
                        <thead class="text-slate-400"><tr><th class="text-left py-1 pr-4 font-medium">Kolom</th><th class="text-left py-1 pr-4 font-medium">Sebelum</th><th class="text-left py-1 font-medium">Sesudah</th></tr></thead>
                        <tbody>
                        @foreach(($new ?? []) as $k => $v)
                            <tr><td class="py-1 pr-4 text-slate-500 whitespace-nowrap">{{ $fields[$k] ?? $k }}</td><td class="py-1 pr-4 text-rose-700 line-through decoration-rose-300">{{ $show($old[$k] ?? null) }}</td><td class="py-1 text-emerald-700">{{ $show($v) }}</td></tr>
                        @endforeach
                        </tbody>
                    @else
                        <tbody>
                        @foreach(($new ?? $old ?? []) as $k => $v)
                            @continue($v === null || $v === '')
                            <tr><td class="py-1 pr-4 text-slate-500 whitespace-nowrap">{{ $fields[$k] ?? $k }}</td><td class="py-1 {{ $log->event === 'deleted' ? 'text-rose-700' : 'text-slate-700' }}">{{ $show($v) }}</td></tr>
                        @endforeach
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    @empty
        <div class="px-5 py-12 text-center text-slate-400 text-sm">Belum ada catatan audit.</div>
    @endforelse
</div>

<div class="mt-4">{{ $logs->links() }}</div>
@endsection
