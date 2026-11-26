@extends('layouts.admin')

@section('content')
@include('network.partials.header', [
    'title' => $installation['fullname'] ?? 'Pemasangan',
    'subtitle' => 'Pengajuan pemasangan #' . ($installation['id'] ?? ''),
    'actions' => [['← Daftar Pemasangan', route('network.installations.index')]],
])

@php
    $rows = [
        'Status' => null,
        'Jenis Layanan' => $types[$installation['service_type'] ?? ''] ?? ($installation['service_type'] ?? '-'),
        'Paket' => $installation['product_name'] ?? '-',
        'Area' => $installation['area_name'] ?? '-',
        'Telepon / WA' => $installation['phone'] ?? '-',
        'Email' => $installation['email'] ?? '-',
        'Alamat' => $installation['address'] ?? '-',
        'Koordinat' => isset($installation['latitude'], $installation['longitude']) ? $installation['latitude'] . ', ' . $installation['longitude'] : '-',
        'Jadwal Pasang' => $installation['scheduled_install_at'] ?? '-',
        'Kode Pelanggan' => $installation['customer_code'] ?? '(dibuat saat diaktifkan)',
        'Catatan' => $installation['notes'] ?? '-',
        'Alasan Ditolak' => $installation['rejection_reason'] ?? null,
        'Diajukan' => $installation['created_at'] ?? '-',
        'Diperbarui' => $installation['updated_at'] ?? '-',
    ];
@endphp

<div class="max-w-3xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
        @foreach($rows as $label => $value)
            @continue($label === 'Alasan Ditolak' && !$value)
            <div class="{{ in_array($label, ['Alamat', 'Catatan']) ? 'sm:col-span-2' : '' }}">
                <dt class="text-xs font-semibold text-slate-400">{{ $label }}</dt>
                <dd class="mt-1 font-bold text-slate-700 break-words">
                    @if($label === 'Status')
                        @include('network.partials.badge', ['value' => $installation['status'] ?? null, 'labels' => $statuses])
                    @else
                        {{ $value }}
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>
    @if(isset($installation['latitude'], $installation['longitude']))
        <a href="https://www.google.com/maps?q={{ $installation['latitude'] }},{{ $installation['longitude'] }}" target="_blank" rel="noopener" class="inline-block mt-6 text-sky-600 font-bold text-xs hover:underline">Buka lokasi di peta →</a>
    @endif
    <p class="mt-6 text-xs text-slate-400">Setelah teknisi selesai memasang, aktifkan instalasi di server jaringan. Pelanggan & layanannya akan otomatis masuk ke Data Pelanggan saat sinkronisasi berikutnya.</p>
</div>
@endsection
