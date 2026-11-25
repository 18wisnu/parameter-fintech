<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Pembayaran langganan NetBill dari pemilik usaha (dikonfirmasi pengelola platform). */
class SubscriptionPayment extends Model
{
    protected $fillable = [
        'business_id', 'plan_id', 'months', 'amount', 'status', 'proof_image', 'notes', 'admin_notes',
        'submitted_by', 'confirmed_by', 'confirmed_at', 'period_start', 'period_end',
    ];

    protected $casts = [
        'amount' => 'float',
        'confirmed_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public const STATUSES = ['pending' => 'Menunggu konfirmasi', 'confirmed' => 'Dikonfirmasi', 'rejected' => 'Ditolak'];

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

    /** Konfirmasi: paket usaha diganti & masa langganan diperpanjang sebanyak `months`. */
    public function confirm(User $by): void
    {
        DB::transaction(function () use ($by) {
            [$start, $end] = $this->business->extendSubscription($this->months, $this->plan);
            $this->update([
                'status' => 'confirmed',
                'confirmed_by' => $by->id,
                'confirmed_at' => now(),
                'period_start' => $start,
                'period_end' => $end,
            ]);
        });
    }
}
