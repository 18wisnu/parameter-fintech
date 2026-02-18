<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_date',
        'account_id', // Jika Anda pakai chart of accounts
        'amount',
        'description',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];
}