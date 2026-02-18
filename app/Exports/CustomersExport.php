<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Customer::all();
    }

    public function headings(): array
    {
        return [
            'ID Database',
            'ID Pelanggan (PPPoE)',
            'Nama Lengkap',
            'Tipe Layanan',
            'No HP',
            'Alamat',
            'Status Isolir',
            'Tanggal Daftar',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->customer_id ?? '-',
            $customer->name,
            strtoupper($customer->type),
            $customer->phone,
            $customer->address,
            $customer->is_isolated ? 'ISOLIR' : 'AKTIF',
            $customer->created_at->format('d-m-Y'),
        ];
    }
}