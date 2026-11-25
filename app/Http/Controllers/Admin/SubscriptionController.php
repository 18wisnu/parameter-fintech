<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Langganan NetBill dari sisi usaha: status, pemakaian, paket & konfirmasi pembayaran.
 * Semua anggota bisa melihat; hanya pemilik yang bisa mengirim pembayaran.
 */
class SubscriptionController extends Controller
{
    public const MONTH_OPTIONS = [1, 3, 6, 12];

    public function index()
    {
        $business = $this->business();

        return view('admin.subscription.index', [
            'business' => $business,
            'status' => $business->subscriptionStatus(),
            'plans' => Plan::where('is_active', true)->orderBy('sort')->orderBy('price')->get(),
            'payments' => $business->subscriptionPayments()->with('plan')->latest()->limit(20)->get(),
            'instructions' => PlatformSetting::get('payment_instructions'),
            'isOwner' => auth()->user()->roleIn($business->id) === 'owner',
            'months' => self::MONTH_OPTIONS,
            'customerCount' => $business->customerCount(),
        ]);
    }

    public function store(Request $request)
    {
        $business = $this->business();
        abort_unless(auth()->user()->roleIn($business->id) === 'owner', 403, 'Hanya pemilik usaha yang bisa memperpanjang langganan.');

        $data = $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'months' => ['required', 'integer', Rule::in(self::MONTH_OPTIONS)],
            'proof_image' => ['required', 'image', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if ($business->subscriptionPayments()->where('status', 'pending')->exists()) {
            return back()->with('error', 'Masih ada pembayaran yang menunggu konfirmasi. Tunggu dikonfirmasi dulu.');
        }

        $plan = Plan::findOrFail($data['plan_id']);
        if ($plan->max_customers && $business->customerCount() > $plan->max_customers) {
            return back()->withInput()->with('error', "Paket {$plan->name} maksimal {$plan->max_customers} pelanggan, sedangkan usaha ini punya {$business->customerCount()}. Pilih paket yang lebih besar.");
        }

        SubscriptionPayment::create([
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'months' => (int) $data['months'],
            'amount' => $plan->price * (int) $data['months'],
            'status' => 'pending',
            'proof_image' => $request->file('proof_image')->store('subscription-proofs', 'public'),
            'notes' => $data['notes'] ?? null,
            'submitted_by' => auth()->id(),
        ]);

        return back()->with('success', 'Bukti pembayaran terkirim. Langganan diperpanjang setelah dikonfirmasi pengelola NetBill.');
    }

    private function business(): Business
    {
        return Business::with('plan')->findOrFail(auth()->user()->current_business_id);
    }
}
