<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $guarded = [];

    // Relasi: Modem ini milik pelanggan siapa?
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relasi: Modem ini nyambung ke Site (OLT) mana?
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}