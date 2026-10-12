<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ProfitDistribution extends Model
{
    use HasFactory, BelongsToTenant;

    // Sesuaikan dengan nama tabel di Migrasi Anda
    protected $table = 'profit_distributions';

    protected $fillable = [
        'business_id',
        'period', 
        'total_revenue', 
        'total_expense', 
        'net_profit', 
        'reserve_fund_amount', 
        'distributable_profit', 
        'share_group_a', 
        'share_group_b',
        'shares_details'
    ];

    protected $casts = [
        'period' => 'date',
        'shares_details' => 'array'
    ];
}