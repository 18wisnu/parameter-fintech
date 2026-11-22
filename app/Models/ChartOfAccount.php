<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    public const KAS = '1001';
    public const PENDAPATAN_INTERNET = '4001';
    public const PENDAPATAN_VOUCHER = '4002';
    public const PENDAPATAN_LAIN = '4003';

    private const DEFAULTS = [
        '1001' => ['Kas Besar (Tunai)', 'asset', true],
        '4001' => ['Pendapatan Internet (PPPoE)', 'revenue', false],
        '4002' => ['Pendapatan Voucher', 'revenue', false],
        '4003' => ['Pendapatan Lain-lain', 'revenue', false],
    ];

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_cash_account',
    ];

    /** Akun berdasarkan kode; akun dasar dibuat otomatis bila belum ada. */
    public static function byCode(string $code): self
    {
        $account = static::where('code', $code)->first();
        if ($account) {
            return $account;
        }

        [$name, $type, $cash] = self::DEFAULTS[$code] ?? throw new \RuntimeException("Akun {$code} tidak ditemukan.");

        return static::create(['code' => $code, 'name' => $name, 'type' => $type, 'is_cash_account' => $cash]);
    }
}
