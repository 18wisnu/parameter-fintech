<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use App\Traits\Auditable;

/**
 * Baris buku kas. Konvensi akun:
 *  - income : account_id = akun pendapatan/modal, source_account_id = kas/bank penerima
 *  - expense: account_id = akun biaya,           source_account_id = kas/bank pembayar
 */
class Transaction extends Model
{
    use HasFactory, BelongsToTenant, Auditable;

    public const PAYMENT_METHODS = [
        'cash' => 'Tunai',
        'collector' => 'Setoran kolektor',
        'transfer' => 'Transfer bank',
        'qris' => 'QRIS / e-wallet',
        'other' => 'Lainnya',
    ];

    protected $fillable = [
        'business_id',
        'date',
        'account_id',
        'source_account_id',
        'customer_id',
        'user_id',
        'invoice_id',
        'deposit_id',
        'expense_id',
        'reference',
        'payment_method',
        'proof_image',
        'notes',
        'description',
        'amount',
        'type',
        'is_locked',
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function sourceAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'source_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withoutGlobalScopes();
    }

    /** Petugas: kolektor penyetor / admin pencatat. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class)->withoutGlobalScopes();
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class)->withoutGlobalScopes();
    }

    protected static function booted(): void
    {
        // Tutup buku: transaksi di bulan yang sudah ditutup terkunci (termasuk memindahkan tanggal ke/dari bulan itu)
        static::saving(function (Transaction $trx) {
            $businessId = $trx->business_id ?: auth()->user()?->current_business_id;
            PeriodClosing::assertOpen($businessId, $trx->date);
            if ($trx->exists && $trx->isDirty('date')) {
                PeriodClosing::assertOpen($businessId, $trx->getOriginal('date'));
            }
        });
        static::deleting(fn (Transaction $trx) => PeriodClosing::assertOpen($trx->business_id, $trx->date));
    }

    public function auditLabel(): string
    {
        return trim(($this->reference ? $this->reference . ' — ' : '') . ($this->description ?? '')
            . ' (Rp ' . number_format((float) $this->amount, 0, ',', '.') . ')');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class)->withoutGlobalScopes();
    }

    public function signedAmount(): float
    {
        return match ($this->type) {
            'income' => (float) $this->amount,
            'expense' => -(float) $this->amount,
            default => 0.0,
        };
    }
}
