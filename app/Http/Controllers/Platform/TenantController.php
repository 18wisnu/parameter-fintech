<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Panel pengelola NetBill: semua usaha yang terdaftar & langganannya. */
class TenantController extends Controller
{
    public function index(Request $request)
    {
        $businesses = Business::with(['plan', 'owner'])->orderBy('name')->get();

        $customerCounts = Customer::withoutGlobalScope('tenant')->subscribers()
            ->selectRaw('business_id, COUNT(*) as n')->groupBy('business_id')->pluck('n', 'business_id');
        $staffCounts = DB::table('business_user')
            ->selectRaw('business_id, COUNT(*) as n')->groupBy('business_id')->pluck('n', 'business_id');
        $lastActive = DB::table('users')->join('business_user', 'business_user.user_id', '=', 'users.id')
            ->join('sessions', 'sessions.user_id', '=', 'users.id')
            ->selectRaw('business_user.business_id, MAX(sessions.last_activity) as t')
            ->groupBy('business_user.business_id')->pluck('t', 'business_id');

        $rows = $businesses->map(fn (Business $b) => (object) [
            'business' => $b,
            'status' => $b->subscriptionStatus(),
            'days_left' => $b->daysLeft(),
            'customers' => (int) ($customerCounts[$b->id] ?? 0),
            'staff' => (int) ($staffCounts[$b->id] ?? 0),
            'last_active' => isset($lastActive[$b->id]) ? \Illuminate\Support\Carbon::createFromTimestamp($lastActive[$b->id]) : null,
        ]);

        if (array_key_exists($request->status, Business::SUBSCRIPTION_LABELS)) {
            $rows = $rows->where('status', $request->status);
        }

        $all = $businesses->map->subscriptionStatus()->countBy();
        $stats = [
            'tenants' => $businesses->count(),
            'paying' => (int) ($all['active'] ?? 0),
            'trial' => (int) ($all['trial'] ?? 0),
            'locked' => (int) (($all['expired'] ?? 0) + ($all['suspended'] ?? 0)),
            'mrr' => (float) $businesses->filter(fn ($b) => $b->subscriptionStatus() === 'active')->sum(fn ($b) => $b->plan?->price ?? 0),
            'pending' => SubscriptionPayment::where('status', 'pending')->count(),
            'revenue_month' => (float) SubscriptionPayment::where('status', 'confirmed')
                ->where('confirmed_at', '>=', now()->startOfMonth())->sum('amount'),
        ];

        return view('platform.tenants.index', ['rows' => $rows->values(), 'stats' => $stats, 'labels' => Business::SUBSCRIPTION_LABELS]);
    }

    public function show(Business $business)
    {
        return view('platform.tenants.show', [
            'business' => $business->load(['plan', 'owner']),
            'status' => $business->subscriptionStatus(),
            'members' => $business->users()->orderBy('name')->get(),
            'customerCount' => $business->customerCount(),
            'payments' => $business->subscriptionPayments()->with(['plan', 'confirmer', 'submitter'])->latest()->get(),
            'plans' => Plan::orderBy('sort')->orderBy('price')->get(),
            'labels' => Business::SUBSCRIPTION_LABELS,
        ]);
    }

    /** Perpanjang manual (mis. bayar tunai), tercatat sebagai pembayaran yang dikonfirmasi. */
    public function extend(Request $request, Business $business)
    {
        $data = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'months' => 'required|integer|min:1|max:36',
            'amount' => 'nullable|numeric|min:0',
            'admin_notes' => 'nullable|string|max:255',
        ]);
        $plan = Plan::findOrFail($data['plan_id']);

        $payment = SubscriptionPayment::create([
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'months' => (int) $data['months'],
            'amount' => $data['amount'] ?? $plan->price * (int) $data['months'],
            'status' => 'pending',
            'method' => 'manual',
            'admin_notes' => $data['admin_notes'] ?? 'Diperpanjang manual oleh pengelola',
            'submitted_by' => auth()->id(),
        ]);
        $payment->confirm(auth()->user());

        return back()->with('success', "Langganan {$business->name} diperpanjang sampai " . $payment->period_end->format('d/m/Y') . '.');
    }

    /** Atur ulang masa coba / tanggal berakhir & paket tanpa mencatat pembayaran. */
    public function update(Request $request, Business $business)
    {
        $data = $request->validate([
            'plan_id' => 'nullable|exists:plans,id',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
        ]);
        $business->forceFill($data)->save();

        return back()->with('success', 'Data langganan diperbarui.');
    }

    public function suspend(Request $request, Business $business)
    {
        $data = $request->validate(['suspend_reason' => 'required|string|max:255']);
        $business->forceFill(['suspended_at' => now(), 'suspend_reason' => $data['suspend_reason']])->save();

        return back()->with('success', "{$business->name} ditangguhkan. Semua menu usaha ini terkunci.");
    }

    public function resume(Business $business)
    {
        $business->forceFill(['suspended_at' => null, 'suspend_reason' => null])->save();

        return back()->with('success', "{$business->name} dibuka kembali.");
    }
}
