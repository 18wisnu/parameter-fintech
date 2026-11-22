<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Services\Finance\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function create()
    {
        $expenseAccounts = ChartOfAccount::where('type', 'expense')->orderBy('code')->get();
        $sourceAccounts = ChartOfAccount::where('type', 'asset')->where('is_cash_account', true)->orderBy('code')->get();

        return view('admin.expenses.create', compact('expenseAccounts', 'sourceAccounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
            'account_id' => 'required|integer|exists:chart_of_accounts,id',
            'source_account_id' => 'required|integer|exists:chart_of_accounts,id',
            'proof_image' => 'nullable|image|max:4096',
        ]);

        $account = ChartOfAccount::where('type', 'expense')->findOrFail($data['account_id']);
        $source = ChartOfAccount::where('type', 'asset')->where('is_cash_account', true)->findOrFail($data['source_account_id']);

        $path = $request->hasFile('proof_image') ? $request->file('proof_image')->store('expenses', 'public') : null;

        DB::transaction(function () use ($data, $account, $source, $path) {
            $expense = Expense::create([
                'transaction_date' => $data['date'],
                'amount' => $data['amount'],
                'description' => $data['description'],
                'category' => $account->name,
                'account_id' => $account->id,
                'source_account_id' => $source->id,
                'user_id' => auth()->id(),
                'proof_image' => $path,
            ]);

            Ledger::expense($expense, $account, $source, auth()->user(), $data['notes'] ?? null);
        });

        return redirect()->route('reports.history')->with('success', 'Pengeluaran dicatat di buku kas.');
    }
}
