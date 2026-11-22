<?php

namespace App\Services\Finance;

use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\User;

/**
 * Menerima setoran kolektor: tagihan terkait dilunasi (layanan diperpanjang),
 * setoran lain langsung masuk buku kas. Dipakai saat setoran dikirim (tanpa konfirmasi)
 * maupun saat admin mengonfirmasi (bila fitur konfirmasi setoran aktif).
 *
 * @return array{0: string, 1: string} [jenis flash, pesan]
 */
class DepositProcessor
{
    public static function approve(Deposit $deposit, User $approver): array
    {
        if ($deposit->status === 'approved') {
            return ['info', 'Setoran ini sudah diterima.'];
        }

        $deposit->forceFill([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ])->save();
        $deposit->load('user');

        $invoice = $deposit->invoice_id ? Invoice::withoutGlobalScopes()->with('customer')->find($deposit->invoice_id) : null;

        if ($invoice && $invoice->status === 'unpaid') {
            $invoice = Ledger::payInvoice($invoice, 'collector', $approver, $deposit);
            $base = "Setoran diterima, tagihan {$invoice->invoice_number} LUNAS dan tercatat di buku kas.";

            return match ($invoice->renewal_status) {
                'success' => ['success', $base . ' Internet aktif' . ($invoice->renewal_new_expiry ? ' s/d ' . $invoice->renewal_new_expiry->format('d M Y') : '') . '.'],
                'failed' => ['warning', $base . ' Tetapi layanan internet belum berhasil diperpanjang: ' . $invoice->renewal_error . ' Sistem akan mencoba ulang otomatis.'],
                default => ['success', $base],
            };
        }

        Ledger::deposit($deposit, $approver);

        return ['success', 'Setoran diterima dan tercatat di buku kas.'];
    }
}
