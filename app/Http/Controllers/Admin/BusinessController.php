<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Semua tenant tempat email ini terdaftar (owner yang belum ter-link sudah di-link oleh middleware)
        $businesses = $user->businesses()->orderBy('businesses.name')->get();

        $invitations = \App\Models\BusinessInvitation::for($user)->with('business', 'inviter')->latest()->get();

        return view('admin.business.select', compact('businesses', 'invitations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Yang menambahkan tenant otomatis menjadi owner tenant tersebut
        Business::registerFor(Auth::user(), $request->name);

        // Redirect to master setting as requested
        return redirect()->route('admin.profit_sharing.index')->with('success', 'Bisnis berhasil dibuat. Silakan atur konfigurasi awal.');
    }

    public function select(Business $business)
    {
        $user = Auth::user();
        
        // Check via pivot relationship
        $isLinked = $user->businesses->contains($business->id);
        // Fallback: check direct ownership
        $isOwner = $business->owner_id == $user->id;

        if (!$isLinked && !$isOwner) {
            abort(403, 'Anda tidak memiliki akses ke bisnis ini.');
        }

        if ($business->isPendingDeletion()) {
            return redirect()->route('admin.business.index')->with('error', "Usaha {$business->name} sedang menunggu dihapus dan tidak bisa dibuka.");
        }

        // Auto-link owner to pivot table if missing
        if ($isOwner && !$isLinked) {
            $user->businesses()->syncWithoutDetaching([$business->id => ['role' => 'owner']]);
        }

        $user->switchBusiness($business);

        // Redirect berdasarkan PIVOT ROLE (per bisnis, bukan users.role)
        $pivotRole = $user->roleIn($business->id);

        if (in_array($pivotRole, ['teknisi', 'kolektor'])) {
            return redirect()->route('mobile.home');
        }

        return redirect()->route('dashboard');
    }
}
