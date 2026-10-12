<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal',
            'Tipe Arus',
            'Kategori / Akun',
            'Keterangan',
            'Pelanggan',
            'Petugas / Kasir',
            'Nominal (Rp)',
        ];
    }

    public function map($trx): array
    {
        return [
            $trx->id,
            $trx->date ? \Carbon\Carbon::parse($trx->date)->format('d-m-Y') : '-',
            $trx->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
            $trx->account->name ?? ($trx->category ?? '-'),
            $trx->description ?? '-',
            $trx->customer->name ?? '-',
            $trx->user->name ?? '-',
            (float)$trx->amount,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
