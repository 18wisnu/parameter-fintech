<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
        'base_salary',
        'current_business_id',
    ];

    public function businesses()
    {
        return $this->belongsToMany(Business::class)->withPivot('role', 'base_salary')->withTimestamps();
    }

    /** Bisnis yang didaftarkan (dimiliki) user ini. */
    public function ownedBusinesses()
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    public function currentBusiness()
    {
        return $this->belongsTo(Business::class, 'current_business_id');
    }

    /** Role yang valid untuk kolom users.role (enum). */
    public const STAFF_ROLES = ['owner', 'admin', 'teknisi', 'kolektor'];

    /**
     * Role yang bisa diberikan lewat menu Pegawai.
     * 'owner' tidak termasuk: owner adalah orang yang mendaftarkan tenant (businesses.owner_id).
     */
    public const ASSIGNABLE_ROLES = ['admin', 'teknisi', 'kolektor'];

    /** Role user di tenant tertentu (dari pivot business_user), null jika tidak terdaftar. */
    public function roleIn($businessId): ?string
    {
        if (!$businessId) {
            return null;
        }

        return \Illuminate\Support\Facades\DB::table('business_user')
            ->where('business_id', $businessId)
            ->where('user_id', $this->id)
            ->value('role');
    }

    /**
     * Pemilik & admin punya akses yang sama di usaha ini, kecuali pengaturan
     * API server jaringan (khusus pemilik).
     */
    public function isManagerIn($businessId = null): bool
    {
        return in_array($this->roleIn($businessId ?? $this->current_business_id), ['owner', 'admin'], true);
    }

    public function belongsToBusiness($businessId): bool
    {
        return $this->roleIn($businessId) !== null;
    }

    /**
     * Jadikan tenant ini aktif. users.role disamakan dengan role di tenant tersebut,
     * sehingga semua pengecekan auth()->user()->role otomatis mengikuti tenant aktif.
     */
    public function switchBusiness($business): void
    {
        $businessId = $business instanceof Business ? $business->id : $business;
        $role = $this->roleIn($businessId);

        $this->current_business_id = $businessId;
        if (in_array($role, self::STAFF_ROLES, true)) {
            $this->role = $role;
        }

        if ($this->isDirty()) {
            $this->save();
        }
    }

    /**
     * Pastikan user punya tenant aktif yang valid (masih terdaftar di pivot).
     * Mengembalikan false jika user belum terhubung ke tenant manapun.
     */
    public function ensureCurrentBusiness(): bool
    {
        // Data lama: owner yang belum tercatat di pivot
        $unlinkedOwned = Business::where('owner_id', $this->id)
            ->whereDoesntHave('users', fn ($q) => $q->where('users.id', $this->id))
            ->pluck('id');
        foreach ($unlinkedOwned as $id) {
            $this->businesses()->attach($id, ['role' => 'owner']);
        }

        if ($this->current_business_id && $this->belongsToBusiness($this->current_business_id)) {
            $this->switchBusiness($this->current_business_id); // sinkronkan role
            return true;
        }

        $first = $this->businesses()->orderBy('businesses.id')->first();
        if ($first) {
            $this->switchBusiness($first);
            return true;
        }

        if ($this->current_business_id) {
            $this->current_business_id = null;
            $this->save();
        }

        return false;
    }

    /** Filter user yang terdaftar di tenant tertentu (opsional: dengan role tertentu di tenant itu). */
    public function scopeInBusiness($query, $businessId, ?array $roles = null)
    {
        return $query->whereHas('businesses', function ($q) use ($businessId, $roles) {
            $q->where('businesses.id', $businessId);
            if ($roles) {
                $q->whereIn('business_user.role', $roles);
            }
        });
    }

    /**
     * Anggota tenant beserta data per-tenant-nya. Atribut `role` dan `base_salary`
     * pada hasil query diambil dari pivot business_user (bukan kolom users).
     */
    public function scopeMembersOf($query, $businessId, ?array $roles = null)
    {
        return $query->join('business_user as bu', 'bu.user_id', '=', 'users.id')
            ->where('bu.business_id', $businessId)
            ->when($roles, fn ($q) => $q->whereIn('bu.role', $roles))
            ->select('users.*', 'bu.role as role', 'bu.base_salary as base_salary');
    }

    public function scopeMembersOfCurrentBusiness($query, ?array $roles = null)
    {
        return $query->membersOf(auth()->user()?->current_business_id, $roles);
    }

    /** Filter user yang terdaftar di tenant yang sedang aktif. */
    public function scopeInCurrentBusiness($query, ?array $roles = null)
    {
        return $query->inBusiness(auth()->user()?->current_business_id, $roles);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean', // pengelola NetBill; sengaja tidak fillable
        ];
    }

    // Relasi ke profil pelanggan
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function isOnline()
    {
        return \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $this->id)
            ->where('last_activity', '>', now()->subMinutes(5)->getTimestamp())
            ->exists();
    }
}
