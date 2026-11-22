@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => 'Voucher ' . $voucher['code'],
    'subtitle' => ($voucher['product']['name'] ?? '-') . ' · ' . ($voucher['entity']['name'] ?? ''),
    'actions' => [['← Daftar Voucher', route('network.vouchers.index')]],
])

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            @php
                $p = $voucher['product'] ?? [];
                $rows = [
                    'Status' => null,
                    'Kode / Username' => $voucher['code'],
                    'Password' => $voucher['password'] ?? '-',
                    'Batch' => $voucher['batch_code'] ?? '-',
                    'Harga Jual' => 'Rp ' . number_format($voucher['selling_price'] ?? ($p['price'] ?? 0), 0, ',', '.'),
                    'Kecepatan' => $p['bandwidth_name'] ?? '-',
                    'Batas' => ($p['limit_type'] ?? '') === 'time' ? (($p['time_limit'] ?? '-') . ' ' . ($p['time_unit'] ?? '')) : ($p['limit_type'] ?? '-'),
                    'Masa Berlaku' => ($voucher['validity'] ?? '-') . ' ' . ($voucher['validity_unit'] ?? ''),
                    'Area Login' => $voucher['restriction_name'] ?? 'Global',
                    'Kunci MAC' => !empty($voucher['mac_binding']) ? ('Ya ' . ($voucher['mac_address'] ?? '')) : 'Tidak',
                    'Login Pertama' => $voucher['first_login_at'] ?? '-',
                    'Kedaluwarsa' => $voucher['expires_at'] ?? '-',
                    'Total Sesi' => $voucher['usage']['total_sessions'] ?? 0,
                    'Total Waktu' => isset($voucher['usage']['total_time']) ? gmdate('H:i:s', (int) $voucher['usage']['total_time']) : '-',
                ];
            @endphp
            @foreach($rows as $label => $value)
            <div>
                <dt class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $label }}</dt>
                <dd class="mt-1 font-bold text-slate-700 break-words">
                    @if($label === 'Status')
                        @include('network.partials.badge', ['value' => $voucher['status'] ?? null, 'labels' => $statuses])
                        @if(!empty($voucher['usage']['is_online']))<span class="ml-1 text-[10px] font-black text-emerald-600">● ONLINE</span>@endif
                    @else
                        {{ $value }}
                    @endif
                </dd>
            </div>
            @endforeach
        </dl>
        @if(!empty($voucher['disabled_at']))
            <p class="mt-6 text-xs text-rose-600 font-bold">Dinonaktifkan {{ $voucher['disabled_at'] }}{{ !empty($voucher['disabled_reason']) ? ' — ' . $voucher['disabled_reason'] : '' }}</p>
        @endif
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold text-slate-800 mb-4">Aktif / Nonaktif</h3>
            @if(($voucher['is_active'] ?? true) && ($voucher['status'] ?? '') !== 'disabled')
            <form action="{{ route('network.vouchers.status', $voucher['code']) }}" method="POST" class="space-y-3" onsubmit="return confirm('Nonaktifkan voucher ini? Pengguna yang sedang memakai akan ditolak saat login berikutnya.')">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="inactive">
                <input type="text" name="reason" placeholder="Alasan (opsional)" class="w-full px-3 py-2 rounded-xl border-slate-200 text-sm">
                <button class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2.5 rounded-xl text-xs font-black uppercase tracking-widest">Nonaktifkan</button>
            </form>
            @else
            <form action="{{ route('network.vouchers.status', $voucher['code']) }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="active">
                <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-xs font-black uppercase tracking-widest">Aktifkan Kembali</button>
            </form>
            @endif
        </div>

        @if(!empty($voucher['sale']))
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 text-sm">
            <h3 class="font-bold text-slate-800 mb-3">Penjualan Online</h3>
            <p class="text-slate-600">Order {{ $voucher['sale']['order_code'] ?? '-' }}</p>
            <p class="font-bold text-slate-800">Rp {{ number_format($voucher['sale']['total'] ?? 0, 0, ',', '.') }} · {{ $voucher['sale']['payment_method'] ?? '' }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
