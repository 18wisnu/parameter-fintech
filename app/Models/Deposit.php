<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Deposit extends Model
{
    use HasFactory, BelongsToTenant;

    // Daftar kolom yang boleh diisi oleh Form Controller
    protected $fillable = [
        'business_id',
        'user_id',
        'customer_id',
        'invoice_id',
        'amount',
        'description',
        'proof_image',
        'status',
        'approved_by',
        'approved_at'
    ];

    // Relasi: Setiap Deposit pasti milik satu User (Teknisi)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}