<?php

namespace App\Traits;

use App\Services\Audit\AuditTrail;

/**
 * Catat setiap tambah/ubah/hapus model ke jejak audit (activity_logs):
 * siapa, kapan, nilai lama → nilai baru.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($model) => AuditTrail::record($model, 'created'));
        static::updated(fn ($model) => AuditTrail::record($model, 'updated'));
        static::deleted(fn ($model) => AuditTrail::record($model, 'deleted'));
    }

    /** Nama data yang mudah dibaca di jejak audit. */
    public function auditLabel(): string
    {
        return '#' . $this->getKey();
    }
}
