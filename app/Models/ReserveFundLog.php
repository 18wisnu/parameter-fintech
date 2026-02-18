<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReserveFundLog extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'amount', 'description', 'transaction_date'];

    protected $casts = [
        'transaction_date' => 'date',
    ];
}