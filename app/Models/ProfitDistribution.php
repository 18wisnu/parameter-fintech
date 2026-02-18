<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitDistribution extends Model
{
    use HasFactory;

    // Sesuaikan dengan nama tabel di Migrasi Anda
    protected $table = 'profit_distributions';

    protected $fillable = [
        'period', 
        'total_revenue', 
        'total_expense', 
        'net_profit', 
        'reserve_fund_amount', 
        'distributable_profit', 
        'share_group_a', 
        'share_group_b'
    ];

    protected $casts = [
        'period' => 'date',
    ];
}