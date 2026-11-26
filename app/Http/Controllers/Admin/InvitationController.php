<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessInvitation;
use Illuminate\Support\Facades\DB;

/** Menerima / menolak undangan bergabung (oleh penerima) dan membatalkannya (oleh usaha pengundang). */
class InvitationController extends Controller
{
    private function findForCurrentUser(BusinessInvitation $invitation): BusinessInvitation
    {
        $user = auth()->user();

        if (strtolower($invitation->email) !== strtolower($user->email)) {
            abort(403, 'Undangan ini bukan untuk akun Anda.');
        }
        if ($invitation->status !== 'pending' || !$invitation->business) {
            abort(410, 'Undangan ini sudah tidak berlaku.');
        }

        return $invitation;
    }

    public function accept(BusinessInvitation $invitation)
    {
        $invitation = $this->findForCurrentUser($invitation);
        $user = auth()->user();
        $business = $invitation->business;

        DB::transaction(function () use ($invitation, $user, $business) {
            if (!$user->belongsToBusiness($business->id)) {
                $user->businesses()->attach($business->id, [
                    'role' => $invitation->role,
                    'base_salary' => $invitation->base_salary,
                ]);
            }

            $invitation->update(['status' => 'accepted', 'responded_at' => now()]);
        });

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'activity' => "Menerima undangan bergabung ke {$business->name} sebagai {$invitation->roleLabel()}",
            'ip_address' => request()->ip(),
        ]);

        // Belum punya usaha aktif: langsung masuk ke usaha yang baru diterima
        if (!$user->current_business_id || !$user->belongsToBusiness($user->current_business_id)) {
            $user->switchBusiness($business);
        }

        return redirect()->route('admin.business.index')
            ->with('success', "Anda sekarang tergabung di {$business->name} sebagai {$invitation->roleLabel()}. Pilih usaha tersebut untuk mulai bekerja.");
    }

    public function decline(BusinessInvitation $invitation)
    {
        $invitation = $this->findForCurrentUser($invitation);
        $invitation->update(['status' => 'declined', 'responded_at' => now()]);

        return redirect()->back()->with('success', "Undangan dari {$invitation->business->name} ditolak.");
    }

    /** Dibatalkan oleh pemilik/admin usaha yang mengundang (dari menu Pegawai). */
    public function cancel(BusinessInvitation $invitation)
    {
        if ((int) $invitation->business_id !== (int) auth()->user()->current_business_id) {
            abort(403);
        }
        if ($invitation->status === 'pending') {
            $invitation->update(['status' => 'cancelled', 'responded_at' => now()]);
        }

        return redirect()->back()->with('success', "Undangan untuk {$invitation->email} dibatalkan.");
    }
}
