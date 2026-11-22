<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Voucher {{ $batch->batch_code }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 12px; color: #0f172a; background: #fff; }
        .toolbar { display: flex; gap: 8px; align-items: center; margin-bottom: 12px; flex-wrap: wrap; }
        .toolbar button { background: #0369a1; color: #fff; border: 0; padding: 8px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .toolbar span { font-size: 12px; color: #64748b; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 6px; }
        .card { border: 1px dashed #64748b; border-radius: 6px; padding: 8px; text-align: center; page-break-inside: avoid; break-inside: avoid; }
        .biz { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; color: #0369a1; }
        .pkg { font-size: 10px; color: #475569; margin-top: 2px; }
        .code { font-family: "Courier New", monospace; font-size: 18px; font-weight: bold; letter-spacing: 2px; margin: 6px 0 2px; }
        .pass { font-family: "Courier New", monospace; font-size: 12px; }
        .price { font-size: 13px; font-weight: bold; margin-top: 4px; }
        @media print {
            .toolbar { display: none; }
            body { padding: 0; }
            .grid { grid-template-columns: repeat(5, 1fr); gap: 4px; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Cetak</button>
        <span>{{ $batch->quantity }} voucher · {{ $batch->product_name }} · {{ $batch->batch_code }}</span>
    </div>
    <div class="grid">
        @foreach($batch->vouchers as $v)
        <div class="card">
            <div class="biz">{{ $businessName }}</div>
            <div class="pkg">{{ $batch->product_name }}</div>
            <div class="code">{{ $v['code'] }}</div>
            @if($batch->login_method === 'user_pass' && !empty($v['password']))
                <div class="pass">Password: {{ $v['password'] }}</div>
            @endif
            <div class="price">Rp {{ number_format($batch->price, 0, ',', '.') }}</div>
        </div>
        @endforeach
    </div>
</body>
</html>
