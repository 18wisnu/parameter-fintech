<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odp extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke Site (OLT)
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    // Relasi ke ODP Parent (Cascading)
    public function parent()
    {
        return $this->belongsTo(Odp::class, 'parent_odp_id');
    }

    // Relasi ke ODP Children
    public function children()
    {
        return $this->hasMany(Odp::class, 'parent_odp_id');
    }

    // Relasi ke Pelanggan (Devices)
    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
