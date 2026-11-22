<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Expense extends Model
{
    use HasFactory, BelongsToTenant;

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
}
