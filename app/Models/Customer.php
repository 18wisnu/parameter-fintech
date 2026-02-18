<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // Tambahkan 'customer_id', 'due_date', 'is_isolated' ke sini
    protected $fillable = [
        'customer_id', // <--- PENTING! Ini yang bikin ID bisa tersimpan
        'name',
        'type',
        'package_fee', 
        'phone',
        'address',
        'due_date',
        'is_isolated',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}