<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(protected $transactions)
    {
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'ID', 'Tanggal', 'Jam Dicatat', 'Arus', 'Akun', 'Kas / Bank', 'Keterangan', 'Catatan',
            'Pelanggan', 'Kode Pelanggan', 'Petugas', 'Referensi', 'Metode', 'Masuk (Rp)', 'Keluar (Rp)', 'Bukti',
        ];
    }

    public function map($trx): array
    {
        return [
            $trx->id,
            $trx->date ? \Carbon\Carbon::parse($trx->date)->format('d-m-Y') : '-',
            $trx->created_at?->format('H:i'),
            $trx->type === 'income' ? 'Pemasukan' : ($trx->type === 'expense' ? 'Pengeluaran' : 'Transfer'),
            $trx->account->name ?? '-',
            $trx->sourceAccount->name ?? '-',
            $trx->description ?? '-',
            $trx->notes ?? '',
            $trx->customer->name ?? '',
            $trx->customer?->displayCode() ?? '',
            $trx->user->name ?? '',
            $trx->reference ?? '',
            Transaction::PAYMENT_METHODS[$trx->payment_method] ?? '',
            $trx->type === 'income' ? (float) $trx->amount : null,
            $trx->type === 'expense' ? (float) $trx->amount : null,
            $trx->proof_image ? asset('storage/' . $trx->proof_image) : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
