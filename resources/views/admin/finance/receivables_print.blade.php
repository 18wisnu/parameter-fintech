<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Tagih</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #111; margin: 16px; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px; vertical-align: top; text-align: left; }
        th { background: #f3f4f6; font-size: 11px; }
        .r { text-align: right; white-space: nowrap; }
        .check { width: 90px; }
        .toolbar { margin-bottom: 12px; }
        .toolbar button { padding: 8px 14px; border: 0; border-radius: 8px; background: #0f172a; color: #fff; font-weight: bold; cursor: pointer; }
        @media print { .toolbar { display: none; } body { margin: 0; } @page { size: A4; margin: 12mm; } }
    </style>
</head>
<body>
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $collectorName = null;
    if (!empty($filters['collector_id'])) {
        $collectorName = $filters['collector_id'] === 'none' ? 'Belum ada kolektor' : optional($collectorOptions->firstWhere('id', (int) $filters['collector_id']))->name;
    }
@endphp
<div class="toolbar"><button onclick="window.print()">Cetak</button></div>

<h1>Daftar Tagih</h1>
<div class="muted">
    {{ $appSettings['business_name'] ?? '' }} · dicetak {{ now()->format('d/m/Y H:i') }}
    @if($collectorName) · Kolektor: <b>{{ $collectorName }}</b>@endif
    @if(!empty($filters['area'])) · Area: <b>{{ $filters['area'] }}</b>@endif
    @if(!empty($filters['bucket'])) · Umur: <b>{{ $bucketLabels[$filters['bucket']] ?? '' }}</b>@endif
</div>
<div style="margin-top:6px"><b>{{ $rows->count() }}</b> pelanggan · <b>{{ $invoice_count }}</b> tagihan · total <b>{{ $rp($total) }}</b></div>

<table>
    <thead>
        <tr><th>#</th><th>Pelanggan</th><th>Alamat / HP</th><th>Bulan</th><th class="r">Jumlah</th><th class="check">Paraf / dibayar</th></tr>
    </thead>
    <tbody>
    @forelse($rows as $i => $row)
        @php $c = $row->customer; @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td><b>{{ $c->name }}</b><br><span class="muted">{{ $c->displayCode() }}</span></td>
            <td>{{ $c->address }}@if($c->phone)<br>{{ $c->phone }}@endif</td>
            <td>{{ $row->invoices->map(fn ($inv) => \Carbon\Carbon::parse($inv->period_date)->locale('id')->translatedFormat('M y'))->implode(', ') }}<br><span class="muted">{{ $row->days }} hari</span></td>
            <td class="r"><b>{{ $rp($row->total) }}</b></td>
            <td></td>
        </tr>
    @empty
        <tr><td colspan="6" style="text-align:center" class="muted">Tidak ada tunggakan.</td></tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
