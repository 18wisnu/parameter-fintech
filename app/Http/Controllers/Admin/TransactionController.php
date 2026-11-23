<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Hapus satu baris buku kas.
     *  - Bulan yang sudah tutup buku: ditolak (lihat Transaction::booted).
     *  - Transaksi terkunci (pembayaran tagihan / setoran kolektor) hanya bisa dihapus pemilik.
     *  - Penghapusan tercatat di jejak audit beserta isi transaksi.
     */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id); // tenant scope

        $user = auth()->user();
        if ($transaction->is_locked && $user->roleIn($user->current_business_id) !== 'owner') {
            return back()->with('error', 'Transaksi dari pembayaran tagihan / setoran kolektor hanya bisa dihapus oleh pemilik.');
        }

        $reopened = false;
        DB::transaction(function () use ($transaction, &$reopened) {
            $expenseId = $transaction->expense_id;
            $invoice = $transaction->invoice;
            $transaction->delete();
            if ($expenseId) {
                Expense::whereKey($expenseId)->first()?->delete();
            }
            // Pembayaran tagihan dihapus → tagihan kembali belum bayar (kecuali masih ada pembayaran lain)
            if ($invoice && $invoice->status === 'paid' && !Transaction::where('invoice_id', $invoice->id)->exists()) {
                $invoice->update(['status' => 'unpaid']);
                $reopened = true;
            }
        });

        $msg = 'Transaksi dihapus dari buku kas dan tercatat di jejak audit.';
        if ($reopened) {
            $msg .= ' Tagihannya kembali berstatus belum bayar. Masa aktif internet yang sudah diperpanjang di server tidak dibatalkan otomatis.';
        }

        return redirect()->route('reports.history')->with($reopened ? 'warning' : 'success', $msg);
    }
}
