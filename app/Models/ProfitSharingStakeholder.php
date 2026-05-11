<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitSharingStakeholder extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'percentage', 'business_id'];
}
