<?php

namespace App\Traits;

use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    public static function bootBelongsToTenant()
    {
        // 1. Global Scope: Otomatis filter berdasarkan unit bisnis yang sedang aktif
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && Auth::user()->current_business_id) {
                $builder->where($builder->getModel()->getTable() . '.business_id', Auth::user()->current_business_id);
            }
        });

        // 2. Model Creating: Otomatis isi business_id saat data baru dibuat
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->current_business_id && empty($model->business_id)) {
                $model->business_id = Auth::user()->current_business_id;
            }
        });
    }

    // Relasi ke Unit Bisnis
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
