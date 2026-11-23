<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use App\Traits\Auditable;

class Customer extends Model
{
    use HasFactory, BelongsToTenant, Auditable;

    public const TYPES = ['pppoe' => 'PPPoE', 'hotspot' => 'Hotspot', 'general' => 'DHCP / Static', 'reseller' => 'Reseller'];
    public const STATUSES = ['active' => 'Aktif', 'isolated' => 'Isolir', 'inactive' => 'Nonaktif'];
    public const BILLING_TYPES = ['prepaid' => 'Prabayar', 'postpaid' => 'Pascabayar'];
    public const BILLING_CYCLES = ['monthly' => 'Bulanan', 'quarterly' => '3 Bulan', 'semi_annually' => '6 Bulan', 'annually' => 'Tahunan'];

    protected $fillable = [
        'business_id',
        'customer_id',
        'name',
        'type',
        'package_fee',
        'product_name',
        'bandwidth',
        'billing_type',
        'billing_cycle',
        'phone',
        'email',
        'address',
        'latitude',
        'longitude',
        'notes',
        'entity_name',
        'due_date',
        'is_isolated',
        'activated_by_id',
        'collector_id',
        'discount',
        'installation_fee',
        'installation_billed',
        'start_date',
        'code',
        'status',
        'mixradius_id',
        'mixradius_service_id',
        'mixradius_username',
        'mixradius_entity_id',
        'mixradius_synced_at',
    ];

    protected $casts = [
        'mixradius_synced_at' => 'datetime',
        'is_isolated' => 'boolean',
        'installation_billed' => 'boolean',
        'start_date' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /** Sinkron otomatis (scheduler) memperbarui pelanggan tiap jam: tidak dicatat di jejak audit. */
    public bool $auditConsole = false;

    public function auditLabel(): string
    {
        return $this->name . ($this->displayCode() ? ' (' . $this->displayCode() . ')' : '');
    }

    /** Kolektor penanggung jawab penagihan. */
    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function activatedBy()
    {
        return $this->belongsTo(User::class, 'activated_by_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Relasi ke akun login (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Terhubung ke server jaringan (hasil sinkron / dibuat dari aplikasi). */
    public function isLinked(): bool
    {
        return !empty($this->mixradius_id);
    }

    /** Kode yang ditampilkan: kode jaringan, lalu ID pelanggan lokal. */
    public function displayCode(): ?string
    {
        return $this->code ?: $this->customer_id;
    }

    /** Status efektif: flag isolir lama tetap dihormati. */
    public function displayStatus(): string
    {
        if ($this->status === 'inactive') {
            return 'inactive';
        }

        return ($this->is_isolated || $this->status === 'isolated') ? 'isolated' : 'active';
    }

    public function mapUrl(): ?string
    {
        return ($this->latitude && $this->longitude)
            ? "https://www.google.com/maps?q={$this->latitude},{$this->longitude}"
            : null;
    }
}
