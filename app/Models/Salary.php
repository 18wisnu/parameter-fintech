<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Salary extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'business_id',
        'user_id',
        'month',
        'base_salary',
        'activation_bonus',
        'total_amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
