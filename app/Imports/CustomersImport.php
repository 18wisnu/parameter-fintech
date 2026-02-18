<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Pastikan nama kolom di Excel (Heading) sesuai: 'nama', 'tipe', 'hp', 'alamat', 'id_pelanggan'
        return new Customer([
            'customer_id' => $row['id_pelanggan'] ?? null, 
            'name'        => $row['nama'],
            'type'        => strtolower($row['tipe'] ?? 'pppoe'), // Default pppoe
            'phone'       => $row['hp'] ?? null,
            'address'     => $row['alamat'] ?? null,
            'is_isolated' => 0, // Default Aktif
        ]);
    }
}