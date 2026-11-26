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
 * Langganan NetBill dari sisi usaha: status & paket → checkout → halaman pesanan (bayar & unggah bukti).
 * Semua anggota bisa melihat; hanya pemilik & admin yang bisa memesan & membayar.
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
            'plans' => $this->plans(),
            'openOrder' => $this->openOrder($business),
            'payments' => $business->subscriptionPayments()->with('plan')->latest()->limit(20)->get(),
            'isOwner' => $this->isOwner($business),
            'customerCount' => $business->customerCount(),
        ]);
    }

    public function checkout(Request $request)
    {
        $business = $this->business();
        $this->ensureOwner($business);

        if ($open = $this->openOrder($business)) {
            return redirect()->route('subscription.order', $open)->with('error', 'Selesaikan atau batalkan pesanan ini dulu sebelum membuat pesanan baru.');
        }

        $plans = $this->plans();
        $selected = $plans->firstWhere('id', (int) $request->query('plan')) ?? $plans->firstWhere('id', $business->plan_id) ?? $plans->first();
        $months = in_array((int) $request->query('months'), self::MONTH_OPTIONS, true) ? (int) $request->query('months') : 1;

        $today = now()->startOfDay();
        $start = $business->subscription_ends_at && $business->subscription_ends_at->gte($today) ? $business->subscription_ends_at->copy()->addDay() : $today;

        return view('admin.subscription.checkout', [
            'business' => $business,
            'plans' => $plans,
            'selected' => $selected,
            'months' => $months,
            'monthOptions' => self::MONTH_OPTIONS,
            'startDate' => $start,
            'customerCount' => $business->customerCount(),
            'gatewayReady' => (bool) config('netbill.gateway'),
        ]);
    }

    public function placeOrder(Request $request)
    {
        $business = $this->business();
        $this->ensureOwner($business);

        $data = $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'months' => ['required', 'integer', Rule::in(self::MONTH_OPTIONS)],
            'method' => ['required', Rule::in(config('netbill.gateway') ? array_keys(SubscriptionPayment::METHODS) : ['manual_transfer'])],
        ]);

        if ($open = $this->openOrder($business)) {
            return redirect()->route('subscription.order', $open);
        }

        $plan = Plan::findOrFail($data['plan_id']);
        if ($plan->max_customers && $business->customerCount() > $plan->max_customers) {
            return back()->withInput()->with('error', "Paket {$plan->name} maksimal {$plan->max_customers} pelanggan, sedangkan usaha ini punya {$business->customerCount()}. Pilih paket yang lebih besar.");
        }

        $order = SubscriptionPayment::create([
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'months' => (int) $data['months'],
            'amount' => $plan->price * (int) $data['months'],
            'method' => $data['method'],
            'status' => 'awaiting_payment',
            'expires_at' => now()->addHours(config('netbill.order_expiry_hours')),
            'submitted_by' => auth()->id(),
        ]);

        return redirect()->route('subscription.order', $order)->with('success', 'Pesanan dibuat. Silakan lakukan pembayaran.');
    }

    public function order(SubscriptionPayment $payment)
    {
        $business = $this->business();
        abort_unless($payment->business_id === $business->id, 404);

        return view('admin.subscription.order', [
            'order' => $payment->load(['plan', 'submitter']),
            'business' => $business,
            'instructions' => PlatformSetting::get('payment_instructions'),
            'isOwner' => $this->isOwner($business),
        ]);
    }

    public function uploadProof(Request $request, SubscriptionPayment $payment)
    {
        $business = $this->business();
        abort_unless($payment->business_id === $business->id, 404);
        $this->ensureOwner($business);
        abort_unless($payment->status === 'awaiting_payment' && !$payment->isExpired(), 422, 'Pesanan ini tidak menunggu pembayaran.');

        $data = $request->validate([
            'proof_image' => ['required', 'image', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $payment->update([
            'proof_image' => $request->file('proof_image')->store('subscription-proofs', 'public'),
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Bukti pembayaran terkirim. Langganan aktif setelah dikonfirmasi pengelola NetBill (biasanya kurang dari 1×24 jam).');
    }

    public function cancel(SubscriptionPayment $payment)
    {
        $business = $this->business();
        abort_unless($payment->business_id === $business->id, 404);
        $this->ensureOwner($business);
        abort_unless($payment->status === 'awaiting_payment', 422, 'Pesanan ini tidak bisa dibatalkan.');

        $payment->update(['status' => 'cancelled']);

        return redirect()->route('subscription.index')->with('success', 'Pesanan ' . $payment->reference . ' dibatalkan.');
    }

    // ------------------------------------------------------------------

    private function business(): Business
    {
        return Business::with('plan')->findOrFail(auth()->user()->current_business_id);
    }

    private function plans()
    {
        return Plan::where('is_active', true)->orderBy('sort')->orderBy('price')->get();
    }

    private function isOwner(Business $business): bool
    {
        return auth()->user()->isManagerIn($business->id);
    }

    private function ensureOwner(Business $business): void
    {
        abort_unless($this->isOwner($business), 403, 'Hanya pemilik atau admin yang bisa memperpanjang langganan.');
    }

    /** Pesanan yang masih berjalan; pesanan yang lewat batas bayar otomatis dibatalkan. */
    private function openOrder(Business $business): ?SubscriptionPayment
    {
        $open = $business->subscriptionPayments()->whereIn('status', SubscriptionPayment::OPEN)->latest()->first();
        if ($open?->isExpired()) {
            $open->update(['status' => 'cancelled', 'admin_notes' => 'Lewat batas waktu pembayaran']);

            return null;
        }

        return $open;
    }
}
