<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoices->count() === 1 ? 'Struk ' . $invoices->first()->invoice_number : 'Struk (' . $invoices->count() . ')' }}</title>
    @php $w = $paper === '80' ? '80mm' : '58mm'; @endphp
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e2e8f0; font-family: 'Courier New', ui-monospace, monospace; color: #000; font-size: {{ $paper === '80' ? '13px' : '12px' }}; line-height: 1.35; }
        .toolbar { position: sticky; top: 0; z-index: 2; background: #0f172a; color: #fff; padding: 8px 10px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; justify-content: center; font-family: Arial, sans-serif; font-size: 13px; }
        .toolbar a, .toolbar button { color: #fff; text-decoration: none; border: 1px solid #475569; border-radius: 8px; padding: 6px 10px; background: transparent; font-size: 13px; cursor: pointer; }
        .toolbar .on { background: #0284c7; border-color: #0284c7; }
        .toolbar .print { background: #059669; border-color: #059669; font-weight: 700; }
        .receipt { width: {{ $w }}; max-width: 100%; margin: 12px auto; background: #fff; padding: 4mm 3mm; box-shadow: 0 1px 4px rgba(0,0,0,.15); page-break-after: always; }
        .c { text-align: center; }
        .b { font-weight: 700; }
        .big { font-size: 1.25em; }
        .hr { border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; gap: 6px; }
        .row span:last-child { text-align: right; }
        .muted { font-size: .9em; }
        .stamp { border: 2px solid #000; text-align: center; font-weight: 700; letter-spacing: 2px; padding: 2px 0; margin: 6px 0; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .receipt { margin: 0; box-shadow: none; width: {{ $w }}; padding: 2mm 1mm; }
            @page { size: {{ $w }} auto; margin: 0; }
        }
    </style>
</head>
<body>
@php
    $q = request()->except(['format', 'paper']);
    $route = $invoices->count() === 1 && request()->route('id') ? route('invoices.print', ['id' => request()->route('id')] + $q) : route('invoices.printList', $q);
    $sep = str_contains($route, '?') ? '&' : '?';
    $rp = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
@endphp
<div class="toolbar">
    <a href="{{ $route }}">A4</a>
    <a href="{{ $route . $sep }}format=pos&paper=58" class="{{ $paper === '58' ? 'on' : '' }}">POS 58mm</a>
    <a href="{{ $route . $sep }}format=pos&paper=80" class="{{ $paper === '80' ? 'on' : '' }}">POS 80mm</a>
    <button class="print" onclick="window.print()">Cetak</button>
</div>

@forelse($invoices as $inv)
    @php
        $c = $inv->customer;
        $period = \Carbon\Carbon::parse($inv->period_date)->locale('id');
        $pay = $payments[$inv->id] ?? null;
    @endphp
    <div class="receipt">
        <div class="c b big">{{ $business->name }}</div>
        <div class="c muted">Tagihan Layanan Internet</div>
        <div class="hr"></div>
        <div class="row"><span>No</span><span>{{ $inv->invoice_number }}</span></div>
        <div class="row"><span>Tgl</span><span>{{ ($pay?->created_at ?? $inv->created_at)?->format('d/m/Y H:i') }}</span></div>
        <div class="hr"></div>
        <div class="b">{{ $c->name ?? '-' }}</div>
        @if($c?->displayCode())<div class="muted">ID: {{ $c->displayCode() }}</div>@endif
        @if($c?->mixradius_username)<div class="muted">User: {{ $c->mixradius_username }}</div>@endif
        <div class="hr"></div>
        <div>Internet {{ $period->translatedFormat('F Y') }}</div>
        @if($c?->product_name)<div class="muted">{{ $c->product_name }}{{ $c->bandwidth ? ' ' . $c->bandwidth : '' }}</div>@endif
        @if(count($inv->details['lines'] ?? []) > 1)
            @foreach($inv->details['lines'] as $line)
                <div class="row"><span>{{ \Illuminate\Support\Str::limit($line['label'], 22) }}</span><span>{{ $line['amount'] < 0 ? '-' : '' }}{{ $rp(abs($line['amount'])) }}</span></div>
            @endforeach
        @endif
        <div class="row b big" style="margin-top:4px"><span>TOTAL</span><span>{{ $rp($inv->amount) }}</span></div>
        @if($inv->status === 'paid')
            <div class="stamp">LUNAS</div>
            @if($pay)
                <div class="row muted"><span>Metode</span><span>{{ \App\Models\Transaction::PAYMENT_METHODS[$pay->payment_method] ?? '-' }}</span></div>
                @if($pay->user)<div class="row muted"><span>Petugas</span><span>{{ $pay->user->name }}</span></div>@endif
            @endif
            @if($inv->renewal_new_expiry)<div class="row muted"><span>Aktif s/d</span><span>{{ $inv->renewal_new_expiry->format('d/m/Y') }}</span></div>@endif
        @elseif($inv->status === 'void')
            <div class="stamp">LUNAS</div>
            <div class="muted c">{{ $inv->void_reason }}</div>
        @else
            <div class="stamp">BELUM BAYAR</div>
            @if($c?->due_date)<div class="row muted"><span>Jatuh tempo</span><span>{{ \Carbon\Carbon::parse($c->due_date)->format('d/m/Y') }}</span></div>@endif
        @endif
        <div class="hr"></div>
        <div class="c muted">{{ $inv->status === 'unpaid' ? 'Bayar sebelum jatuh tempo agar internet tidak terputus.' : 'Terima kasih.' }}</div>
    </div>
@empty
    <div class="receipt c">Tidak ada invoice.</div>
@endforelse
</body>
</html>
