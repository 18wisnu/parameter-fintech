<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $guarded = []; // Mengizinkan semua kolom diisi

    // Relasi: Satu Site (OLT) punya banyak Device (Modem)
    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}