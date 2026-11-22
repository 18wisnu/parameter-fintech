<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #1e293b; }
        h2 { margin: 0; text-align: center; }
        p.sub { text-align: center; margin: 2px 0 10px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e8f0; padding: 5px; vertical-align: top; }
        th { background: #f1f5f9; text-align: left; }
        .r { text-align: right; }
        .muted { color: #64748b; }
        tfoot td { font-weight: bold; background: #f8fafc; }
    </style>
</head>
<body>
    <h2>Buku Kas</h2>
    <p class="sub">{{ request('start_date') ?: 'Awal' }} s/d {{ request('end_date') ?: now()->format('Y-m-d') }} · dicetak {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Akun</th>
                <th>Pelanggan</th>
                <th>Petugas</th>
                <th>Ref / Metode</th>
                <th class="r">Masuk</th>
                <th class="r">Keluar</th>
            </tr>
        </thead>
        <tbody>
        @foreach($transactions as $trx)
            <tr>
                <td>{{ \Carbon\Carbon::parse($trx->date)->format('d/m/Y') }}<div class="muted">{{ $trx->created_at?->format('H:i') }}</div></td>
                <td>{{ $trx->description }}@if($trx->notes)<div class="muted">{{ $trx->notes }}</div>@endif</td>
                <td>{{ $trx->account->name ?? '-' }}</td>
                <td>{{ $trx->customer->name ?? '-' }}</td>
                <td>{{ $trx->user->name ?? '-' }}</td>
                <td>{{ $trx->reference ?? '-' }}<div class="muted">{{ \App\Models\Transaction::PAYMENT_METHODS[$trx->payment_method] ?? '' }}</div></td>
                <td class="r">{{ $trx->type === 'income' ? number_format($trx->amount, 0, ',', '.') : '' }}</td>
                <td class="r">{{ $trx->type === 'expense' ? number_format($trx->amount, 0, ',', '.') : '' }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="r">Total</td>
                <td class="r">{{ number_format($transactions->where('type', 'income')->sum('amount'), 0, ',', '.') }}</td>
                <td class="r">{{ number_format($transactions->where('type', 'expense')->sum('amount'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
