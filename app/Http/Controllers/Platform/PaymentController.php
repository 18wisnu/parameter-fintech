<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;

/** Pembayaran langganan dari pemilik usaha: konfirmasi / tolak. */
class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $status = array_key_exists($request->status, SubscriptionPayment::STATUSES) ? $request->status : 'pending';

        $payments = SubscriptionPayment::with(['business', 'plan', 'submitter', 'confirmer'])
            ->where('status', $status)->latest()->paginate(30)->withQueryString();

        return view('platform.payments.index', ['payments' => $payments, 'status' => $status, 'statuses' => SubscriptionPayment::STATUSES]);
    }

    public function confirm(SubscriptionPayment $payment)
    {
        abort_unless($payment->status === 'pending', 422, 'Pembayaran ini sudah diproses.');
        $payment->confirm(auth()->user());

        return back()->with('success', "Pembayaran {$payment->business->name} dikonfirmasi. Langganan aktif sampai " . $payment->period_end->format('d/m/Y') . '.');
    }

    public function reject(Request $request, SubscriptionPayment $payment)
    {
        abort_unless($payment->status === 'pending', 422, 'Pembayaran ini sudah diproses.');
        $data = $request->validate(['admin_notes' => 'required|string|max:255']);
        $payment->update(['status' => 'rejected', 'admin_notes' => $data['admin_notes'], 'confirmed_by' => auth()->id(), 'confirmed_at' => now()]);

        return back()->with('success', 'Pembayaran ditolak.');
    }
}
