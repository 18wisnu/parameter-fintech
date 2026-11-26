<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Undangan bergabung ke usaha. Sengaja TANPA scope tenant: penerima undangan
 * membacanya dari luar usaha tersebut.
 */
class BusinessInvitation extends Model
{
    protected $fillable = ['business_id', 'email', 'role', 'base_salary', 'invited_by', 'status', 'responded_at'];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** Undangan yang menunggu jawaban untuk user ini. */
    public function scopeFor($query, User $user)
    {
        return $query->pending()->where('email', strtolower($user->email))->whereHas('business');
    }

    public function roleLabel(): string
    {
        return ['admin' => 'Admin', 'teknisi' => 'Teknisi', 'kolektor' => 'Kolektor'][$this->role] ?? ucfirst($this->role);
    }
}
