<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pelanggan NetBill = pemilik usaha yang memakai NetBill (akun pemilik beserta usahanya).
 * Data pelanggan internet milik masing-masing usaha tidak ditampilkan, hanya jumlahnya.
 */
class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $rows = $this->rows($request);

        if ($request->query('export') === 'csv') {
            return $this->csv($rows);
        }

        return view('platform.customers.index', [
            'rows' => $rows,
            'labels' => Business::SUBSCRIPTION_LABELS,
            'summary' => [
                'owners' => $rows->count(),
                'businesses' => $rows->sum(fn ($r) => $r->businesses->count()),
                'paying' => $rows->filter(fn ($r) => $r->businesses->contains(fn ($b) => $b->subscriptionStatus() === 'active'))->count(),
                'new_month' => $rows->filter(fn ($r) => $r->user->created_at?->gte(now()->startOfMonth()))->count(),
            ],
        ]);
    }

    private function rows(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $owners = User::query()
            ->whereHas('ownedBusinesses')
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")
                ->orWhereHas('ownedBusinesses', fn ($b) => $b->where('name', 'like', "%{$q}%"))))
            ->with(['ownedBusinesses' => fn ($b) => $b->with('plan')->orderBy('name')])
            ->orderByDesc('created_at')
            ->get();

        $customerCounts = Customer::withoutGlobalScope('tenant')->subscribers()
            ->selectRaw('business_id, COUNT(*) as n')->groupBy('business_id')->pluck('n', 'business_id');
        $lastSeen = DB::table('sessions')->whereNotNull('user_id')
            ->selectRaw('user_id, MAX(last_activity) as t')->groupBy('user_id')->pluck('t', 'user_id');

        $rows = $owners->map(fn (User $u) => (object) [
            'user' => $u,
            'businesses' => $u->ownedBusinesses,
            'customers' => (int) $u->ownedBusinesses->sum(fn ($b) => $customerCounts[$b->id] ?? 0),
            'last_seen' => isset($lastSeen[$u->id]) ? \Illuminate\Support\Carbon::createFromTimestamp($lastSeen[$u->id]) : null,
        ]);

        if (array_key_exists($status = (string) $request->query('status'), Business::SUBSCRIPTION_LABELS)) {
            $rows = $rows->filter(fn ($r) => $r->businesses->contains(fn ($b) => $b->subscriptionStatus() === $status));
        }

        return $rows->values();
    }

    private function csv($rows)
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['Nama', 'Email', 'Login Google', 'Usaha', 'Paket', 'Status', 'Berakhir', 'Jumlah pelanggan internet', 'Terdaftar', 'Terakhir aktif']);
        foreach ($rows as $r) {
            foreach ($r->businesses as $b) {
                fputcsv($out, [
                    $r->user->name, $r->user->email, $r->user->google_id ? 'ya' : 'tidak',
                    $b->name, $b->plan->name ?? '-', Business::SUBSCRIPTION_LABELS[$b->subscriptionStatus()],
                    $b->accessEndsAt()?->format('Y-m-d') ?? '-', $b->customerCount(),
                    $r->user->created_at?->format('Y-m-d'), $r->last_seen?->format('Y-m-d H:i') ?? '-',
                ]);
            }
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response("\xEF\xBB\xBF" . $csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="pelanggan-netbill-' . now()->format('Ymd') . '.csv"',
        ]);
    }
}
