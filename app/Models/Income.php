<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Income extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'business_id',
        'transaction_date',
        'account_id', // Jika Anda pakai chart of accounts
        'amount',
        'description',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];
}