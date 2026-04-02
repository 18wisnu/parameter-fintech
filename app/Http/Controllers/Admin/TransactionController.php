<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Income; // Tambahkan ini Bos!
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function destroy($id)
    {
        // Cari di tabel incomes karena datanya nyelip di sana
        $income = Income::find($id);
        if ($income) {
            $income->delete();
        }

        // Cari juga di tabel transactions untuk jaga-jaga
        $transaction = Transaction::find($id);
        if ($transaction) {
            $transaction->delete();
        }

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'activity' => 'Mengahapus data history keuangan ID: ' . $id,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('reports.history')->with('success', 'Data bersih total, Bos!');
    }
}