<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Pesanan langganan NetBill (checkout) dari pemilik usaha.
 * awaiting_payment → (bukti diunggah / dibayar) pending → confirmed | rejected; atau cancelled.
 */
class SubscriptionPayment extends Model
{
    protected $fillable = [
        'reference', 'business_id', 'plan_id', 'months', 'amount', 'method', 'gateway_ref', 'status', 'expires_at', 'paid_at',
        'proof_image', 'notes', 'admin_notes', 'submitted_by', 'confirmed_by', 'confirmed_at', 'period_start', 'period_end',
    ];

    protected $casts = [
        'amount' => 'float',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public const STATUSES = [
        'awaiting_payment' => 'Menunggu pembayaran',
        'pending' => 'Menunggu konfirmasi',
        'confirmed' => 'Lunas',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];

    /** Metode bayar. 'gateway' aktif setelah payment gateway dipasang (config netbill.gateway). */
    public const METHODS = [
        'manual_transfer' => 'Transfer bank (konfirmasi manual)',
        'gateway' => 'QRIS / Virtual Account / E-wallet',
    ];

    /** Pesanan yang masih berjalan (belum selesai). */
    public const OPEN = ['awaiting_payment', 'pending'];

    protected static function booted(): void
    {
        static::created(function (self $p) {
            if (!$p->reference) {
                $p->forceFill(['reference' => 'NB' . $p->created_at->format('ymd') . '-' . str_pad((string) $p->id, 5, '0', STR_PAD_LEFT)])->saveQuietly();
            }
        });
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN, true);
    }

    /** Batas bayar lewat & belum ada bukti. */
    public function isExpired(): bool
    {
        return $this->status === 'awaiting_payment' && $this->expires_at && $this->expires_at->isPast();
    }

    /** Konfirmasi: paket usaha diganti & masa langganan diperpanjang sebanyak `months`. */
    public function confirm(User $by): void
    {
        DB::transaction(function () use ($by) {
            [$start, $end] = $this->business->extendSubscription($this->months, $this->plan);
            $this->update([
                'status' => 'confirmed',
                'paid_at' => $this->paid_at ?? now(),
                'confirmed_by' => $by->id,
                'confirmed_at' => now(),
                'period_start' => $start,
                'period_end' => $end,
            ]);
        });
    }
}
