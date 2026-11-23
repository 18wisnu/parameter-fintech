<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use App\Traits\Auditable;

class Deposit extends Model
{
    use HasFactory, BelongsToTenant, Auditable;

    protected $fillable = [
        'business_id',
        'user_id',
        'customer_id',
        'invoice_id',
        'amount',
        'description',
        'proof_image',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function auditLabel(): string
    {
        return 'STR-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT) . ' ' . ($this->description ?? '')
            . ' (Rp ' . number_format((float) $this->amount, 0, ',', '.') . ')';
    }

    /** Kolektor / teknisi penyetor. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withoutGlobalScopes();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class)->withoutGlobalScopes();
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
