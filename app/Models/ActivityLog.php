<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log aktivitas & jejak audit per tenant.
 * Baris audit punya event (created/updated/deleted), model_type, model_id dan payload {old, new}.
 */
class ActivityLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'activity',
        'event',
        'model_type',
        'model_id',
        'payload',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
