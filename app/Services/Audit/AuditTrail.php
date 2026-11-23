<?php

namespace App\Services\Audit;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditTrail
{
    /** Nama jenis data untuk tampilan. */
    public const LABELS = [
        'Transaction' => 'Transaksi',
        'Invoice' => 'Tagihan',
        'Customer' => 'Pelanggan',
        'Deposit' => 'Setoran',
        'Expense' => 'Pengeluaran',
        'CashClosing' => 'Tutup kas',
        'PeriodClosing' => 'Tutup buku',
    ];

    private const EVENTS = ['created' => 'Tambah', 'updated' => 'Ubah', 'deleted' => 'Hapus'];

    /** Kolom yang tidak perlu dicatat (berubah otomatis / tidak bermakna). */
    private const IGNORED = ['created_at', 'updated_at', 'mixradius_synced_at', 'remember_token', 'password'];

    public static function record(Model $model, string $event): void
    {
        // Sinkron otomatis tanpa pengguna (scheduler) untuk model yang sering berubah tidak dicatat
        if (!Auth::check() && app()->runningInConsole() && property_exists($model, 'auditConsole') && !$model->auditConsole) {
            return;
        }

        $old = $new = null;
        if ($event === 'updated') {
            $changed = array_diff(array_keys($model->getChanges()), self::IGNORED);
            if (!$changed) {
                return;
            }
            foreach ($changed as $key) {
                $old[$key] = $model->getOriginal($key);
                $new[$key] = $model->getAttribute($key);
            }
        } elseif ($event === 'created') {
            $new = array_diff_key($model->getAttributes(), array_flip(self::IGNORED));
        } else {
            $old = array_diff_key($model->getAttributes(), array_flip(self::IGNORED));
        }

        $type = class_basename($model);
        $label = method_exists($model, 'auditLabel') ? $model->auditLabel() : '#' . $model->getKey();

        $log = new ActivityLog([
            'user_id' => Auth::id(),
            'activity' => sprintf('%s %s: %s', self::EVENTS[$event] ?? $event, strtolower(self::LABELS[$type] ?? $type), $label),
            'event' => $event,
            'model_type' => $type,
            'model_id' => $model->getKey(),
            'payload' => ['old' => self::clean($old), 'new' => self::clean($new)],
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
        $log->business_id = $model->getAttribute('business_id') ?: Auth::user()?->current_business_id;
        $log->save();
    }

    private static function clean(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return array_map(fn ($v) => $v instanceof \DateTimeInterface ? $v->format('Y-m-d H:i:s') : (is_scalar($v) || $v === null ? $v : json_encode($v)), $values);
    }
}
