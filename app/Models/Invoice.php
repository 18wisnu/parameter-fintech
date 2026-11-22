<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Invoice extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'business_id',
        'customer_id',
        'invoice_number', // Format: INV-202602-001
        'period_date',    // Periode (2026-02-01)
        'amount',
        'status',         // unpaid, paid
        'renewal_status', // null, pending, success, failed (perpanjangan layanan internet)
        'renewal_error',
        'renewal_attempts',
        'renewal_expiry_before',
        'renewal_new_expiry',
        'renewal_remote_invoice',
        'renewal_payment_method',
        'renewal_note',
        'renewed_at',
    ];

    protected $casts = [
        'renewal_expiry_before' => 'date',
        'renewal_new_expiry' => 'date',
        'renewed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}