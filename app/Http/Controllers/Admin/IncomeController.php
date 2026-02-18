<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\Income; 
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\NewIncomeNotification; 
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Notification;

class IncomeController extends Controller
{
    public function create()
    {
        // Gunakan nama variabel $accounts agar pas dengan baris 24 di blade Bos
        $accounts = ChartOfAccount::whereIn('type', ['revenue', 'equity'])->get();

        // Dropdown tujuan (Kas Besar/Bank)
        $targetAccounts = ChartOfAccount::where('type', 'asset')
            ->where('is_cash_account', true)
            ->get();

        return view('admin.incomes.create', compact('accounts', 'targetAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'              => 'required|date',
            'amount'            => 'required|numeric|min:100',
            'description'       => 'required|string',
            'account_id'        => 'required', 
            'target_account_id' => 'required', 
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Simpan Laporan
                $income = Income::create([
                    'transaction_date' => $request->date,
                    'amount'           => $request->amount,
                    'description'      => $request->description,
                    'account_id'       => $request->account_id,
                ]);

                // 2. Update Saldo Dashboard
                Transaction::create([
                    'date'              => $request->date,
                    'amount'            => $request->amount,
                    'description'       => $request->description,
                    'account_id'        => $request->account_id,        
                    'source_account_id' => $request->target_account_id, 
                    'type'              => 'income', 
                    'is_locked'         => false,
                ]);

                // 3. Kirim Notif Lonceng
                $admins = User::all();
                Notification::send($admins, new NewIncomeNotification($income));
            });

            return redirect()->route('dashboard')->with('success', 'Modal berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}