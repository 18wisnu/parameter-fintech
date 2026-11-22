<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoices->count() === 1 ? 'Invoice ' . $invoices->first()->invoice_number : 'Cetak Invoice (' . $invoices->count() . ')' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; color: #1e293b; background: #f1f5f9; font-size: 13px; }
        .toolbar { position: sticky; top: 0; background: #0f172a; color: #fff; padding: 10px 16px; display: flex; gap: 8px; align-items: center; justify-content: space-between; }
        .toolbar button, .toolbar a { background: #0284c7; color: #fff; border: 0; border-radius: 8px; padding: 8px 14px; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: none; }
        .toolbar a { background: transparent; border: 1px solid #475569; }
        .sheet { max-width: 720px; margin: 16px auto; padding: 0 12px; }
        .inv { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; margin-bottom: 16px; page-break-inside: avoid; position: relative; overflow: hidden; }
        .head { display: flex; justify-content: space-between; gap: 16px; border-bottom: 2px solid {{ $business->theme_color ?: '#0284c7' }}; padding-bottom: 12px; margin-bottom: 14px; }
        .biz { font-size: 20px; font-weight: 900; color: {{ $business->theme_color ?: '#0284c7' }}; }
        .muted { color: #64748b; font-size: 12px; }
        .title { text-align: right; }
        .title b { font-size: 18px; letter-spacing: 2px; }
        .grid { display: flex; gap: 24px; margin-bottom: 14px; }
        .grid > div { flex: 1; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; font-weight: 700; margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; border-bottom: 1px solid #e2e8f0; padding: 8px 0; }
        td { padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
        .right { text-align: right; }
        .total { font-size: 18px; font-weight: 900; }
        .stamp { position: absolute; right: 28px; bottom: 56px; transform: rotate(-12deg); border: 3px solid; border-radius: 8px; padding: 4px 14px; font-size: 22px; font-weight: 900; letter-spacing: 3px; opacity: .8; }
        .paid { color: #059669; border-color: #059669; }
        .unpaid { color: #e11d48; border-color: #e11d48; }
        .foot { margin-top: 14px; font-size: 11px; color: #64748b; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; max-width: none; padding: 0; }
            .inv { border: 1px solid #cbd5e1; border-radius: 0; margin: 0 0 8mm; }
            @page { size: A4; margin: 10mm; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <span>{{ $invoices->count() }} invoice siap dicetak</span>
    <div style="display:flex;gap:8px">
        @php
            $route = request()->route('id') ? route('invoices.print', ['id' => request()->route('id')] + request()->except(['format', 'paper'])) : route('invoices.printList', request()->except(['format', 'paper']));
            $sep = str_contains($route, '?') ? '&' : '?';
        @endphp
        <a href="{{ $route . $sep }}format=pos&paper=58">Struk POS 58mm</a>
        <a href="{{ $route . $sep }}format=pos&paper=80">POS 80mm</a>
        <button onclick="window.print()">Cetak</button>
    </div>
</div>

<div class="sheet">
@forelse($invoices as $inv)
    @php
        $c = $inv->customer;
        $period = \Carbon\Carbon::parse($inv->period_date)->locale('id');
    @endphp
    <div class="inv">
        <div class="head">
            <div>
                <div class="biz">{{ $business->name }}</div>
                <div class="muted">Tagihan Layanan Internet</div>
            </div>
            <div class="title">
                <b>INVOICE</b>
                <div class="muted">{{ $inv->invoice_number }}</div>
                <div class="muted">Dibuat {{ $inv->created_at?->locale('id')->translatedFormat('d F Y') }}</div>
            </div>
        </div>

        <div class="grid">
            <div>
                <div class="label">Ditagihkan kepada</div>
                <div style="font-weight:700;font-size:15px">{{ $c->name ?? '-' }}</div>
                @if($c?->displayCode())<div class="muted">ID Pelanggan: {{ $c->displayCode() }}</div>@endif
                @if($c?->phone)<div class="muted">{{ $c->phone }}</div>@endif
                @if($c?->address)<div class="muted">{{ $c->address }}</div>@endif
            </div>
            <div>
                <div class="label">Periode</div>
                <div style="font-weight:700">{{ $period->translatedFormat('F Y') }}</div>
                @if($c?->due_date)
                    <div class="label" style="margin-top:8px">Jatuh tempo</div>
                    <div>{{ \Carbon\Carbon::parse($c->due_date)->locale('id')->translatedFormat('d F Y') }}</div>
                @endif
            </div>
        </div>

        <table>
            <thead><tr><th>Keterangan</th><th class="right">Jumlah</th></tr></thead>
            <tbody>
                <tr>
                    <td>
                        Langganan internet {{ $c?->product_name ? '— ' . $c->product_name : '' }}{{ $c?->bandwidth ? ' (' . $c->bandwidth . ')' : '' }}
                        <div class="muted">{{ $period->translatedFormat('F Y') }}</div>
                    </td>
                    <td class="right">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="right" style="border:0"><b>Total</b></td>
                    <td class="right total" style="border:0">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="stamp {{ $inv->status === 'unpaid' ? 'unpaid' : 'paid' }}">{{ $inv->status === 'unpaid' ? 'BELUM BAYAR' : 'LUNAS' }}</div>

        <div class="foot">
            @if($inv->status === 'void')
                {{ $inv->void_reason }}. Terima kasih.
            @elseif($inv->status === 'paid')
                @php $pay = $payments[$inv->id] ?? null; @endphp
                Dibayar {{ ($pay?->created_at ?? $inv->updated_at)?->locale('id')->translatedFormat('d F Y H:i') }}{{ $pay ? ' · ' . (\App\Models\Transaction::PAYMENT_METHODS[$pay->payment_method] ?? '') . ($pay->user ? ' · ' . $pay->user->name : '') : '' }}. Terima kasih atas pembayaran Anda.
            @else
                Mohon lakukan pembayaran sebelum jatuh tempo agar layanan internet tidak terputus.
            @endif
        </div>
    </div>
@empty
    <div class="inv" style="text-align:center;color:#94a3b8">Tidak ada invoice untuk dicetak.</div>
@endforelse
</div>
</body>
</html>
