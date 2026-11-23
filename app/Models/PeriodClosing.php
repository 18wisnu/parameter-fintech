<?php

namespace App\Models;

use App\Exceptions\ClosedPeriodException;
use App\Traits\Auditable;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/** Tutup buku bulanan. Selama tercatat di sini, transaksi bulan itu terkunci. */
class PeriodClosing extends Model
{
    use BelongsToTenant, Auditable;

    protected $fillable = ['business_id', 'period', 'revenue', 'expense', 'net', 'cash_balance', 'snapshot', 'notes', 'closed_by'];

    protected $casts = [
        'period' => 'date',
        'snapshot' => 'array',
    ];

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function auditLabel(): string
    {
        return $this->period?->locale('id')->translatedFormat('F Y') ?? '#' . $this->id;
    }

    public static function isClosed(?int $businessId, $date): bool
    {
        if (!$businessId || !$date) {
            return false;
        }

        return static::withoutGlobalScope('tenant')
            ->where('business_id', $businessId)
            ->whereDate('period', Carbon::parse($date)->startOfMonth()->toDateString())
            ->exists();
    }

    /** @throws ClosedPeriodException */
    public static function assertOpen(?int $businessId, $date): void
    {
        if (static::isClosed($businessId, $date)) {
            throw new ClosedPeriodException('Buku bulan ' . Carbon::parse($date)->locale('id')->translatedFormat('F Y')
                . ' sudah ditutup. Transaksi di bulan itu tidak bisa ditambah, diubah atau dihapus. Buka kembali tutup buku bila perlu koreksi.');
        }
    }
}
