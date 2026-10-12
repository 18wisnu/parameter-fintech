<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ReserveFundLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['business_id', 'type', 'amount', 'description', 'transaction_date'];

    protected $casts = [
        'transaction_date' => 'date',
    ];
}