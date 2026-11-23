<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CashClosing;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

/** Daftar tagih kolektor: tagihan belum bayar milik pelanggan yang ditugaskan kepadanya. */
class CollectionController extends Controller
{
    public function index()
    {
        $invoices = Invoice::query()
            ->where('status', 'unpaid')
            ->whereHas('customer', fn ($q) => $q->where('collector_id', Auth::id()))
            ->with('customer')
            ->orderBy('period_date')
            ->get();

        $customers = $invoices->groupBy('customer_id')->map(fn ($items) => (object) [
            'customer' => $items->first()->customer,
            'invoices' => $items,
            'total' => (float) $items->sum('amount'),
        ])->sortBy(fn ($r) => $r->customer->address ?? $r->customer->name)->values();

        $lastClosing = CashClosing::where('user_id', Auth::id())->latest('date')->first();

        return view('mobile.collections.index', compact('customers', 'lastClosing'));
    }
}
