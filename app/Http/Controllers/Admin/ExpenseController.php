<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function create()
    {
        // Gunakan nama $expenseAccounts agar pas dengan baris 33 di blade Bos
        $expenseAccounts = ChartOfAccount::where('type', 'expense')->get();

        // Akun Kas sebagai sumber uang
        $sourceAccounts = ChartOfAccount::where('type', 'asset')
            ->where('is_cash_account', true)
            ->get();

        return view('admin.expenses.create', compact('expenseAccounts', 'sourceAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'              => 'required|date',
            'amount'            => 'required|numeric|min:1',
            'description'       => 'required|string',
            'account_id'        => 'required', 
            'source_account_id' => 'required', 
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Catat Pengeluaran
                Expense::create([
                    'transaction_date' => $request->date,
                    'amount'           => $request->amount,
                    'description'      => $request->description,
                ]);

                // 2. Potong Saldo Jurnal
                Transaction::create([
                    'date'              => $request->date,
                    'amount'            => $request->amount,
                    'description'       => $request->description,
                    'account_id'        => $request->source_account_id, 
                    'target_account_id' => $request->account_id,        
                    'type'              => 'expense',
                    'is_locked'         => false,
                ]);
            });

            return redirect()->route('dashboard')->with('success', 'Pengeluaran berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}