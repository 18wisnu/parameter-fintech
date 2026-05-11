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
        $user = \Illuminate\Support\Facades\Auth::user();
        
        $businesses = \App\Models\Business::where(function($q) use ($user) {
            $q->where('owner_id', $user->id)
              ->orWhereHas('users', function($sq) use ($user) {
                  $sq->where('users.id', $user->id);
              });
        })->get();

        // If list is empty but user is an owner, attempt to link them to their businesses
        if ($businesses->isEmpty() && $user->role == 'owner') {
             $owned = \App\Models\Business::where('owner_id', $user->id)->get();
             foreach($owned as $b) {
                 $user->businesses()->syncWithoutDetaching([$b->id => ['role' => 'owner']]);
             }
             if ($owned->isNotEmpty()) {
                 $businesses = $owned;
             }
        }

        return view('admin.business.select', compact('businesses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        // Create new business
        $business = Business::create([
            'name' => $request->name,
            'owner_id' => $user->id,
            'enabled_features' => [
                'profit_sharing' => true,
                'reserve_fund' => true,
                'salary_management' => true,
                'voucher_analysis' => true,
                'customer_data' => true,
                'invoices' => true,
                'transactions' => true,
                'staff_data' => true,
            ]
        ]);

        // Attach user to business
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        // Set as current
        $user->update(['current_business_id' => $business->id]);

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

        // Auto-link owner to pivot table if missing
        if ($isOwner && !$isLinked) {
            $user->businesses()->syncWithoutDetaching([$business->id => ['role' => 'owner']]);
        }

        $user->update(['current_business_id' => $business->id]);

        // Redirect berdasarkan PIVOT ROLE (per bisnis, bukan users.role)
        $pivotRole = \Illuminate\Support\Facades\DB::table('business_user')
            ->where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->value('role');

        if (in_array($pivotRole, ['teknisi', 'kolektor'])) {
            return redirect()->route('mobile.home');
        }

        return redirect()->route('dashboard');
    }
}
