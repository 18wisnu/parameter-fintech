<?php

namespace App\Services\Finance;

use App\Models\ChartOfAccount;
use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Mixradius\ServiceRenewal;
use Illuminate\Support\Facades\DB;

/**
 * Satu pintu pencatatan buku kas: pembayaran tagihan, setoran kolektor & pengeluaran.
 * Setiap baris menyimpan petugas, sumber (tagihan/setoran/pengeluaran), referensi,
 * metode, bukti dan catatan supaya buku kas bisa ditelusuri lengkap.
 */
class Ledger
{
    /**
     * Lunasi tagihan + catat pemasukan + buka isolir, lalu perpanjang layanan di server.
     * Mengembalikan invoice terbaru (dengan status perpanjangan).
     */
    public static function payInvoice(Invoice $invoice, string $method, ?User $by, ?Deposit $deposit = null, ?string $notes = null): Invoice
    {
        $invoice->loadMissing('customer');

        DB::transaction(function () use ($invoice, $method, $by, $deposit, $notes) {
            $invoice->update(['status' => 'paid', 'void_reason' => null]);

            if ($invoice->customer && $invoice->customer->is_isolated) {
                $invoice->customer->update(['is_isolated' => false, 'status' => $invoice->customer->status === 'isolated' ? 'active' : $invoice->customer->status]);
            }

            $period = \Carbon\Carbon::parse($invoice->period_date)->locale('id')->translatedFormat('F Y');
            $customer = $invoice->customer;

            self::write([
                'business_id' => $invoice->business_id,
                'type' => 'income',
                'account_id' => ChartOfAccount::byCode(ChartOfAccount::PENDAPATAN_INTERNET)->id,
                'source_account_id' => ChartOfAccount::byCode(ChartOfAccount::KAS)->id,
                'amount' => $deposit ? $deposit->amount : $invoice->amount,
                'customer_id' => $invoice->customer_id,
                'user_id' => $deposit?->user_id ?? $by?->id,
                'invoice_id' => $invoice->id,
                'deposit_id' => $deposit?->id,
                'reference' => $invoice->invoice_number,
                'payment_method' => $deposit ? 'collector' : $method,
                'proof_image' => $deposit?->proof_image,
                'description' => sprintf('Pembayaran internet %s — %s%s', $period, $customer->name ?? '-',
                    $customer?->displayCode() ? ' (' . $customer->displayCode() . ')' : ''),
                'notes' => self::joinNotes([
                    $deposit ? 'Setoran oleh ' . ($deposit->user->name ?? '-') : null,
                    $deposit && (float) $deposit->amount !== (float) $invoice->amount
                        ? 'Nominal tagihan Rp ' . number_format($invoice->amount, 0, ',', '.') : null,
                    $by && $deposit && $by->id !== $deposit->user_id ? 'Dikonfirmasi ' . $by->name : null,
                    !$deposit && $by ? 'Dicatat oleh ' . $by->name : null,
                    $notes,
                ]),
                'is_locked' => true,
            ]);
        });

        $label = $deposit ? 'Setoran kolektor: ' . ($deposit->user->name ?? '-') : 'Dibayar via admin: ' . ($by->name ?? '-');

        return ServiceRenewal::afterPaid($invoice->fresh(), $method === 'transfer' ? 'bank_transfer' : 'cash', $label);
    }

    /** Setoran yang tidak terkait tagihan (mis. penjualan voucher reseller). */
    public static function deposit(Deposit $deposit, ?User $by): Transaction
    {
        $deposit->loadMissing('customer', 'user');
        $customer = $deposit->customer;
        $isVoucher = in_array($customer?->type, ['reseller', 'hotspot'], true);

        return self::write([
            'business_id' => $deposit->business_id,
            'type' => 'income',
            'account_id' => ChartOfAccount::byCode($isVoucher ? ChartOfAccount::PENDAPATAN_VOUCHER : ChartOfAccount::PENDAPATAN_LAIN)->id,
            'source_account_id' => ChartOfAccount::byCode(ChartOfAccount::KAS)->id,
            'amount' => $deposit->amount,
            'customer_id' => $deposit->customer_id,
            'user_id' => $deposit->user_id,
            'deposit_id' => $deposit->id,
            'reference' => 'STR-' . str_pad((string) $deposit->id, 5, '0', STR_PAD_LEFT),
            'payment_method' => 'collector',
            'proof_image' => $deposit->proof_image,
            'description' => ($isVoucher ? 'Setoran voucher — ' : 'Setoran — ') . ($deposit->description ?: ($customer->name ?? '-')),
            'notes' => self::joinNotes([
                'Setoran oleh ' . ($deposit->user->name ?? '-'),
                $by && $by->id !== $deposit->user_id ? 'Dikonfirmasi ' . $by->name : null,
            ]),
            'is_locked' => true,
        ]);
    }

    public static function expense(Expense $expense, ChartOfAccount $account, ChartOfAccount $source, ?User $by, ?string $notes = null): Transaction
    {
        return self::write([
            'business_id' => $expense->business_id,
            'date' => $expense->transaction_date,
            'type' => 'expense',
            'account_id' => $account->id,
            'source_account_id' => $source->id,
            'amount' => $expense->amount,
            'user_id' => $by?->id,
            'expense_id' => $expense->id,
            'reference' => 'PGL-' . str_pad((string) $expense->id, 5, '0', STR_PAD_LEFT),
            'payment_method' => $source->code === ChartOfAccount::KAS ? 'cash' : 'transfer',
            'proof_image' => $expense->proof_image,
            'description' => $expense->description,
            'notes' => self::joinNotes([$notes, $by ? 'Dicatat oleh ' . $by->name : null]),
            'is_locked' => false,
        ]);
    }

    private static function write(array $attributes): Transaction
    {
        $trx = new Transaction($attributes + ['date' => now()->toDateString()]);
        if (!empty($attributes['business_id'])) {
            $trx->business_id = $attributes['business_id'];
        }
        $trx->save();

        return $trx;
    }

    private static function joinNotes(array $parts): ?string
    {
        $parts = array_values(array_filter($parts, fn ($p) => $p !== null && $p !== ''));

        return $parts ? implode(' · ', $parts) : null;
    }
}
