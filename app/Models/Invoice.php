<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_number', // Format: INV-202602-001
        'period_date',    // Periode (2026-02-01)
        'amount',
        'status',         // unpaid, paid
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}