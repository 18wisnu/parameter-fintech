@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => $entity['name'] ?? 'Mitra',
    'subtitle' => ($types[$entity['type'] ?? ''] ?? '') . ' · ' . ($entity['code'] ?? '') . (!empty($entity['phone']) ? ' · ' . $entity['phone'] : ''),
    'actions' => [['← Reseller & Outlet', route('network.entities.index')]],
])

@php
    $s = $entity['stats'] ?? [];
    $rp = fn ($v) => 'Rp ' . number_format($v ?? 0, 0, ',', '.');
    $tiles = [
        ['Saldo', $rp($entity['balance'] ?? 0)],
        ['Pendapatan Hari Ini', $rp($s['income_today'] ?? 0)],
        ['Pendapatan Bulan Ini', $rp($s['income_month'] ?? 0)],
        ['Komisi Bulan Ini', $rp($s['commission_month'] ?? 0)],
        ['Stok Voucher', number_format($s['voucher_stock'] ?? 0, 0, ',', '.')],
        ['Voucher Aktif', number_format($s['voucher_active'] ?? 0, 0, ',', '.')],
        ['Voucher Dibuat Bulan Ini', number_format($s['voucher_gen_month'] ?? 0, 0, ',', '.')],
        ['Login Voucher Hari Ini', number_format($s['voucher_login_today'] ?? 0, 0, ',', '.')],
        ['Pelanggan Aktif', ($s['active_customers'] ?? 0) . ' / ' . ($s['total_customers'] ?? 0)],
        ['Online Sekarang', number_format($s['online_sessions'] ?? 0, 0, ',', '.')],
        ['Transaksi Bulan Ini', number_format($s['trx_count_month'] ?? 0, 0, ',', '.')],
        ['Jumlah Outlet', number_format($s['outlet_count'] ?? 0, 0, ',', '.')],
    ];
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach($tiles as [$label, $value])
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <p class="text-xs font-semibold text-slate-400">{{ $label }}</p>
        <p class="mt-2 text-xl font-bold text-slate-800 tabular-nums">{{ $value }}</p>
    </div>
    @endforeach
</div>

<div class="mt-6 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 text-sm text-slate-600">
    <p><span class="font-bold text-slate-800">Sistem saldo:</span> {{ ['prepaid' => 'Deposit (bayar di muka)', 'commission' => 'Komisi', 'none' => 'Tidak ada'][$entity['balance_type'] ?? 'none'] ?? '-' }}
        @if(!empty($entity['credit_limit'])) · Limit kredit {{ $rp($entity['credit_limit']) }}@endif</p>
    @if(!empty($entity['address']))<p class="mt-1"><span class="font-bold text-slate-800">Alamat:</span> {{ $entity['address'] }}</p>@endif
    <p class="mt-1"><span class="font-bold text-slate-800">Terdaftar:</span> {{ $entity['created_at'] ?? '-' }}</p>
</div>
@endsection
