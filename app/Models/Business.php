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
        'deposit_approval' => false, // setoran kolektor perlu dikonfirmasi admin dulu
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
        'trial_ends_at' => 'date',
        'subscription_ends_at' => 'date',
        'suspended_at' => 'datetime',
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
        // Usaha baru mulai dengan masa coba
        $business->forceFill(['trial_ends_at' => now()->addDays(config('netbill.trial_days'))->toDateString()])->save();

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

    /** Fitur aktif? Kunci yang belum pernah diatur memakai nilai default. */
    public function feature(string $key): bool
    {
        $features = $this->enabled_features ?: [];

        return filter_var($features[$key] ?? (self::DEFAULT_FEATURES[$key] ?? true), FILTER_VALIDATE_BOOLEAN);
    }

    public function hasMixradius(): bool
    {
        return $this->mixradius_enabled && $this->mixradius_base_url;
    }

    // ------------------------------------------------------------------
    // Langganan NetBill
    // ------------------------------------------------------------------

    public const SUBSCRIPTION_LABELS = [
        'unlimited' => 'Tanpa batas waktu',
        'trial' => 'Masa coba',
        'active' => 'Aktif',
        'expired' => 'Berakhir',
        'suspended' => 'Ditangguhkan',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptionPayments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    /**
     * unlimited: belum pernah diberi tanggal berakhir (usaha lama / internal)
     * trial / active: masih bisa dipakai
     * expired / suspended: terkunci sampai diperpanjang / dibuka pengelola
     */
    public function subscriptionStatus(): string
    {
        $today = now()->startOfDay();

        return match (true) {
            $this->suspended_at !== null => 'suspended',
            $this->subscription_ends_at && $this->subscription_ends_at->gte($today) => 'active',
            $this->trial_ends_at && $this->trial_ends_at->gte($today) && !$this->subscription_ends_at => 'trial',
            !$this->subscription_ends_at && !$this->trial_ends_at => 'unlimited',
            default => 'expired',
        };
    }

    public function isServiceable(): bool
    {
        return !in_array($this->subscriptionStatus(), ['expired', 'suspended'], true);
    }

    /** Tanggal terakhir boleh dipakai (null = tanpa batas). */
    public function accessEndsAt(): ?\Illuminate\Support\Carbon
    {
        return $this->subscription_ends_at ?? $this->trial_ends_at;
    }

    /** Sisa hari sampai berakhir (negatif = sudah lewat), null bila tanpa batas. */
    public function daysLeft(): ?int
    {
        $end = $this->accessEndsAt();

        return $end ? (int) now()->startOfDay()->diffInDays($end, false) : null;
    }

    /**
     * Perpanjang langganan N bulan. Bila masih aktif, ditambahkan setelah tanggal berakhir;
     * bila sudah lewat / masih trial, dihitung mulai hari ini.
     *
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon} [mulai, berakhir]
     */
    public function extendSubscription(int $months, ?Plan $plan = null): array
    {
        $today = now()->startOfDay();
        $start = $this->subscription_ends_at && $this->subscription_ends_at->gte($today)
            ? $this->subscription_ends_at->copy()->addDay()
            : $today->copy();
        $end = $start->copy()->addMonthsNoOverflow($months)->subDay();

        $this->forceFill(array_filter([
            'subscription_ends_at' => $end->toDateString(),
            'plan_id' => $plan?->id,
        ]))->save();

        return [$start, $end];
    }

    /** Batas jumlah pelanggan dari paket (null = tanpa batas, termasuk masa coba & usaha lama). */
    public function customerLimit(): ?int
    {
        if (in_array($this->subscriptionStatus(), ['unlimited', 'trial'], true)) {
            return null;
        }

        return $this->plan?->max_customers;
    }

    public function customerCount(): int
    {
        return Customer::withoutGlobalScope('tenant')->where('business_id', $this->id)->count();
    }

    /** Masih boleh menambah $n pelanggan secara manual / impor? */
    public function canAddCustomers(int $n = 1): bool
    {
        $limit = $this->customerLimit();

        return $limit === null || $this->customerCount() + $n <= $limit;
    }
}
