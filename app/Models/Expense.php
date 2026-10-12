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
        // Tambahkan field lain jika ada, misal 'category_id'
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];
}