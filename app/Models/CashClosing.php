<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** Tutup kas harian kolektor: uang fisik yang diserahkan vs total setoran tercatat. */
class CashClosing extends Model
{
    use BelongsToTenant, Auditable;

    protected $fillable = ['business_id', 'user_id', 'date', 'deposit_count', 'expected_amount', 'actual_amount', 'difference', 'notes', 'closed_by'];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function auditLabel(): string
    {
        return ($this->user->name ?? 'Kolektor #' . $this->user_id) . ' — ' . $this->date?->format('d/m/Y');
    }
}
