<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'account_id',
        'source_account_id',
        'customer_id',
        'description',
        'amount',
        'type',
        'is_locked',
    ];

    // Relasi ke Akun Utama (Tujuan)
    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    // Relasi ke Akun Sumber (Asal Uang) - INI YANG TADI BIKIN ERROR
    public function sourceAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'source_account_id');
    }
}