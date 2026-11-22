<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Deposit extends Model
{
    use HasFactory, BelongsToTenant;

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
