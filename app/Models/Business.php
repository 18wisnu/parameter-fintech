<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    public const DEFAULT_FEATURES = [
        'profit_sharing' => true,
        'reserve_fund' => true,
        'salary_management' => true,
        'voucher_analysis' => true,
        'customer_data' => true,
        'invoices' => true,
        'transactions' => true,
        'staff_data' => true,
    ];

    protected $fillable = [
        'name',
        'theme_color',
        'enabled_features',
        'reserve_percentage',
        'owner_id',
        'mixradius_enabled',
        'mixradius_base_url',
        'mixradius_api_key',
        'mixradius_api_secret',
        'mixradius_last_sync_at',
    ];

    protected $hidden = [
        'mixradius_api_key',
        'mixradius_api_secret',
    ];

    protected $casts = [
        'enabled_features' => 'array',
        'mixradius_enabled' => 'boolean',
        'mixradius_api_key' => 'encrypted',
        'mixradius_api_secret' => 'encrypted',
        'mixradius_last_sync_at' => 'datetime',
    ];

    /**
     * Daftarkan tenant baru. Yang mendaftarkan otomatis menjadi owner tenant ini
     * dan tenant ini langsung dijadikan tenant aktif.
     */
    public static function registerFor(User $owner, string $name): self
    {
        $business = static::create([
            'name' => $name,
            'owner_id' => $owner->id,
            'enabled_features' => self::DEFAULT_FEATURES,
        ]);

        $owner->businesses()->attach($business->id, ['role' => 'owner']);
        $owner->switchBusiness($business);

        return $business;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role', 'base_salary')->withTimestamps();
    }

    public function hasMixradius(): bool
    {
        return $this->mixradius_enabled && $this->mixradius_base_url;
    }
}
