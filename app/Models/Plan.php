<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Paket langganan NetBill (milik platform, bukan milik tenant). */
class Plan extends Model
{
    protected $fillable = ['name', 'price', 'max_customers', 'description', 'is_active', 'sort'];

    protected $casts = [
        'price' => 'float',
        'max_customers' => 'integer',
        'is_active' => 'boolean',
    ];

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    public function limitLabel(): string
    {
        return $this->max_customers ? 'Maks. ' . number_format($this->max_customers, 0, ',', '.') . ' pelanggan' : 'Pelanggan tanpa batas';
    }
}
