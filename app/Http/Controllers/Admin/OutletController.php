<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;

/**
 * Outlet voucher: penjual voucher yang menyetor ke usaha (sama dengan "Outlet" di server jaringan).
 * Dipisah dari menu Pelanggan dan tidak dihitung dalam batas paket.
 */
class OutletController extends Controller
{
    public function index(Request $request)
    {
        $business = Business::findOrFail(auth()->user()->current_business_id);

        $query = Customer::outlets()->orderBy('name');

        if ($search = trim((string) $request->search)) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"));
        }
        if ($request->status === 'active') {
            $query->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'inactive'));
        } elseif ($request->status === 'inactive') {
            $query->where('status', 'inactive');
        }

        $outlets = $query->paginate(20)->withQueryString();

        // Setoran voucher (akun 4002) per outlet: bulan ini & total
        $account = ChartOfAccount::byCode(ChartOfAccount::PENDAPATAN_VOUCHER);
        $monthStart = now()->startOfMonth()->toDateString();
        $deposits = collect();
        if ($account && $outlets->isNotEmpty()) {
            $deposits = Transaction::where('account_id', $account->id)->where('type', 'income')
                ->whereIn('customer_id', $outlets->pluck('id'))
                ->selectRaw('customer_id, SUM(amount) as total, SUM(CASE WHEN date >= ? THEN amount ELSE 0 END) as month, MAX(date) as last_date', [$monthStart])
                ->groupBy('customer_id')->get()->keyBy('customer_id');
        }

        $counts = [
            'all' => Customer::outlets()->count(),
            'active' => Customer::outlets()->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'inactive'))->count(),
            'inactive' => Customer::outlets()->where('status', 'inactive')->count(),
        ];

        $monthTotal = $account
            ? (float) Transaction::where('account_id', $account->id)->where('type', 'income')
                ->whereHas('customer', fn ($q) => $q->outlets())->where('date', '>=', $monthStart)->sum('amount')
            : 0;

        return view('admin.outlets.index', [
            'outlets' => $outlets,
            'deposits' => $deposits,
            'counts' => $counts,
            'monthTotal' => $monthTotal,
            'network' => $business->hasMixradius(),
        ]);
    }
}
