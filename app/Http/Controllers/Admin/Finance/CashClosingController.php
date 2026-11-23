<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\CashClosing;
use App\Models\Deposit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Tutup kas harian kolektor: bandingkan uang fisik yang diserahkan dengan
 * total setoran yang tercatat di aplikasi pada tanggal itu, simpan selisihnya.
 */
class CashClosingController extends Controller
{
    public function index(Request $request)
    {
        $date = $this->date($request->input('date'));

        $recorded = Deposit::query()
            ->whereDate('created_at', $date)
            ->whereIn('status', ['approved', 'pending'])
            ->selectRaw('user_id, COUNT(*) as n, SUM(amount) as total')
            ->groupBy('user_id')
            ->get()->keyBy('user_id');

        $closings = CashClosing::whereDate('date', $date)->with('closer')->get()->keyBy('user_id');

        $staff = User::membersOfCurrentBusiness()->orderBy('name')->get()
            ->filter(fn ($u) => $u->role === 'kolektor' || $recorded->has($u->id) || $closings->has($u->id));

        $rows = $staff->map(fn ($u) => (object) [
            'user' => $u,
            'count' => (int) ($recorded[$u->id]->n ?? 0),
            'expected' => (float) ($recorded[$u->id]->total ?? 0),
            'closing' => $closings[$u->id] ?? null,
        ])->values();

        $history = CashClosing::with('user')->latest('date')->latest('id')->limit(30)->get();

        $outstanding = CashClosing::query()
            ->selectRaw('user_id, SUM(difference) as diff, COUNT(*) as n')
            ->where('difference', '!=', 0)
            ->groupBy('user_id')
            ->with('user')
            ->get();

        return view('admin.finance.cash_closing', compact('date', 'rows', 'history', 'outstanding'));
    }

    public function store(Request $request)
    {
        $businessId = auth()->user()->current_business_id;

        $data = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'user_id' => ['required', 'integer', Rule::exists('business_user', 'user_id')->where('business_id', $businessId)],
            'actual_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $date = Carbon::parse($data['date'])->toDateString();

        $recorded = Deposit::query()
            ->where('user_id', $data['user_id'])
            ->whereDate('created_at', $date)
            ->whereIn('status', ['approved', 'pending'])
            ->selectRaw('COUNT(*) as n, COALESCE(SUM(amount), 0) as total')
            ->first();

        $expected = (float) $recorded->total;
        $actual = (float) $data['actual_amount'];
        $diff = round($actual - $expected, 2);

        if ($diff != 0 && trim((string) ($data['notes'] ?? '')) === '') {
            return back()->withInput()->with('error', 'Ada selisih kas. Isi catatan penyebab selisihnya.');
        }

        $closing = CashClosing::whereDate('date', $date)->where('user_id', $data['user_id'])->first()
            ?? new CashClosing(['user_id' => $data['user_id'], 'date' => $date]);
        $closing->fill([
            'deposit_count' => (int) $recorded->n,
            'expected_amount' => $expected,
            'actual_amount' => $actual,
            'difference' => $diff,
            'notes' => $data['notes'] ?? null,
            'closed_by' => auth()->id(),
        ])->save();

        $name = User::find($data['user_id'])->name ?? 'kolektor';
        $msg = "Kas {$name} tanggal " . Carbon::parse($date)->format('d/m/Y') . ' ditutup. '
            . ($diff == 0 ? 'Cocok, tidak ada selisih.' : 'Selisih Rp ' . number_format($diff, 0, ',', '.') . ($diff < 0 ? ' (kurang).' : ' (lebih).'));

        return redirect()->route('finance.cash.index', ['date' => $date])->with($diff == 0 ? 'success' : 'warning', $msg);
    }

    private function date(?string $value): string
    {
        try {
            $date = $value ? Carbon::parse($value) : now();
        } catch (\Throwable) {
            $date = now();
        }

        return ($date->isFuture() ? now() : $date)->toDateString();
    }
}
