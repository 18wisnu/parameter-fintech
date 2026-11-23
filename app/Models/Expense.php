<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use App\Traits\Auditable;

class Expense extends Model
{
    use HasFactory, BelongsToTenant, Auditable;

    protected $fillable = [
        'business_id',
        'transaction_date',
        'amount',
        'description',
        'category',
        'account_id',
        'source_account_id',
        'user_id',
        'proof_image',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function auditLabel(): string
    {
        return ($this->description ?? '') . ' (Rp ' . number_format((float) $this->amount, 0, ',', '.') . ')';
    }
}
