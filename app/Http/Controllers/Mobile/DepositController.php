<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Deposit;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Finance\DepositProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    // 0. Riwayat Lengkap (Semua Hari)
    public function index()
    {
        $riwayat = Deposit::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('mobile.deposits.index', compact('riwayat'));
    }

    // 1. Form setoran
    public function create(Request $request)
    {
        // Pelanggan yang ditugaskan ke kolektor ini tampil paling atas
        $customers = Customer::orderByRaw('CASE WHEN collector_id = ? THEN 0 ELSE 1 END', [Auth::id()])
            ->orderBy('name', 'asc')->get();

        $selected = $customers->firstWhere('id', (int) $request->query('customer_id'));
        $preset = [
            'customer_id' => $selected?->id,
            'amount' => $selected ? ((int) $request->query('amount') ?: null) : null,
            'group' => $selected ? (in_array($selected->type, ['reseller', 'hotspot'], true) ? 'VOUCHER' : 'MONTHLY') : null,
        ];

        return view('mobile.deposits.create', compact('customers', 'preset'));
    }

    // 2. Simpan setoran. Tanpa fitur konfirmasi, setoran langsung diterima & masuk buku kas.
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'customer_id' => 'required|integer',
            'notes' => 'nullable|string|max:255',
            'proof_image' => 'nullable|image|max:2048',
        ]);

        $customer = Customer::findOrFail($request->customer_id); // tenant scope: hanya pelanggan unit ini

        $path = $request->hasFile('proof_image')
            ? $request->file('proof_image')->store('deposits', 'public')
            : null;

        // Tagihan belum lunas yang paling lama dulu
        $invoice = Invoice::where('customer_id', $customer->id)
            ->where('status', 'unpaid')
            ->orderBy('period_date')
            ->first();

        $description = $customer->name . ($request->notes ? ' (' . $request->notes . ')' : '');

        $deposit = Deposit::create([
            'user_id' => Auth::id(),
            'customer_id' => $customer->id,
            'invoice_id' => $invoice?->id,
            'amount' => $request->amount,
            'description' => $description,
            'proof_image' => $path,
            'status' => 'pending',
        ]);

        $business = Business::find(Auth::user()->current_business_id);
        if ($business && !$business->feature('deposit_approval')) {
            [$type, $message] = DepositProcessor::approve($deposit, Auth::user());

            return redirect()->route('mobile.home')->with($type === 'warning' ? 'success' : $type, $message);
        }

        return redirect()->route('mobile.home')->with('success', 'Setoran terkirim, menunggu konfirmasi admin.');
    }
}
