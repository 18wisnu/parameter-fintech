<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Business;
use App\Services\BusinessDataEraser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/** Reset Data & Hapus Usaha. Khusus pemilik usaha (lihat routes/web.php). */
class DangerZoneController extends Controller
{
    private function business(): Business
    {
        return Business::findOrFail(Auth::user()->current_business_id);
    }

    /** Pemilik harus mengetik nama usaha persis untuk tindakan yang menghapus data. */
    private function confirmName(Request $request, Business $business): void
    {
        if (trim((string) $request->confirm_name) !== trim($business->name)) {
            throw ValidationException::withMessages([
                'confirm_name' => 'Nama usaha yang diketik tidak sama. Ketik persis: ' . $business->name,
            ]);
        }
    }

    private function log(Business $business, string $activity): void
    {
        (new ActivityLog())->forceFill([
            'user_id' => Auth::id(),
            'business_id' => $business->id,
            'activity' => $activity,
            'ip_address' => request()->ip(),
        ])->save();
    }

    public function index()
    {
        $business = $this->business();

        return view('admin.danger.index', [
            'business' => $business,
            'categories' => BusinessDataEraser::CATEGORIES,
            'counts' => BusinessDataEraser::counts($business),
            'graceDays' => Business::DELETION_GRACE_DAYS,
        ]);
    }

    public function reset(Request $request)
    {
        $business = $this->business();
        $request->validate([
            'categories' => 'required|array|min:1',
            'categories.*' => 'in:' . implode(',', array_keys(BusinessDataEraser::CATEGORIES)),
        ], ['categories.required' => 'Pilih minimal satu jenis data yang akan dihapus.']);
        $this->confirmName($request, $business);

        $deleted = BusinessDataEraser::reset($business, $request->categories);

        $summary = collect($deleted)
            ->map(fn ($n, $key) => BusinessDataEraser::CATEGORIES[$key]['label'] . " ({$n})")
            ->implode(', ');

        $this->log($business, "Reset data usaha: {$summary}");

        return redirect()->route('admin.danger.index')->with('success', "Data berhasil dihapus: {$summary}.");
    }

    public function destroy(Request $request)
    {
        $business = $this->business();
        $this->confirmName($request, $business);

        $business->scheduleDeletion(Auth::user());

        $this->log($business, 'Meminta hapus usaha, dijadwalkan ' . $business->deletion_scheduled_at->format('d/m/Y'));

        // Pindah ke usaha lain (kalau ada); kalau tidak, ke halaman Pilih usaha
        $user = Auth::user();
        $user->current_business_id = null;
        $user->save();
        $user->ensureCurrentBusiness();

        return redirect()->route('admin.business.index')->with('success',
            "Usaha {$business->name} dikunci dan akan dihapus permanen pada " . $business->deletion_scheduled_at->translatedFormat('d F Y')
            . '. Sampai tanggal itu Anda masih bisa membatalkannya di halaman ini.');
    }

    /** Batalkan penghapusan. Bisa dari halaman Pilih usaha walau usaha sedang terkunci. */
    public function restore(Business $business)
    {
        if ((int) $business->owner_id !== (int) Auth::id()) {
            abort(403, 'Hanya pemilik usaha yang bisa membatalkan penghapusan.');
        }
        if (!$business->isPendingDeletion()) {
            return redirect()->route('admin.business.index');
        }

        $business->cancelDeletion();

        $this->log($business, 'Membatalkan penghapusan usaha');

        return redirect()->route('admin.business.index')->with('success', "Penghapusan {$business->name} dibatalkan. Usaha bisa dipakai lagi seperti biasa.");
    }
}
