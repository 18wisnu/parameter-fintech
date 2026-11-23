<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use App\Traits\Auditable;

class Invoice extends Model
{
    use HasFactory, BelongsToTenant, Auditable;

    protected $fillable = [
        'business_id',
        'customer_id',
        'invoice_number', // Format: INV-202602-001
        'period_date',    // Periode (2026-02-01)
        'amount',
        'details',        // rincian: paket, prorata, diskon, biaya pasang
        'status',         // unpaid, paid, void (sudah dibayar di server jaringan)
        'void_reason',
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
        'details' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function auditLabel(): string
    {
        return $this->invoice_number . ' (Rp ' . number_format((float) $this->amount, 0, ',', '.') . ')';
    }

    /** Baris rincian: [['label' => ..., 'amount' => ...], ...]. Tagihan lama: satu baris paket. */
    public function lines(): array
    {
        return $this->details['lines'] ?? [['label' => 'Paket internet', 'amount' => (float) $this->amount]];
    }
}