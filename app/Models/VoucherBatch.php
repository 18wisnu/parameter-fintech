<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class VoucherBatch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'business_id', 'batch_code', 'product_id', 'product_name', 'price', 'quantity',
        'prefix', 'login_method', 'restriction_name', 'vouchers', 'notes', 'created_by',
    ];

    protected $casts = [
        'vouchers' => 'encrypted:array',
        'price' => 'decimal:2',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
