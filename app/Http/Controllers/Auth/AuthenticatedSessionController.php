<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Auto-select business if user has only one or already has active business
        $user = Auth::user();
        if (!$user->current_business_id) {
            // Check if user has associated businesses or owns one
            $firstBusiness = $user->businesses()->first();
            if (!$firstBusiness) {
                $firstBusiness = \App\Models\Business::where('owner_id', $user->id)->first();
                if ($firstBusiness) {
                    $user->businesses()->syncWithoutDetaching([$firstBusiness->id => ['role' => 'owner']]);
                }
            }

            if ($firstBusiness) {
                $user->update(['current_business_id' => $firstBusiness->id]);
            }
        }

        // If user already has a current_business_id, redirect based on their role/dashboard
        if ($user->current_business_id) {
            $pivotRole = \Illuminate\Support\Facades\DB::table('business_user')
                ->where('business_id', $user->current_business_id)
                ->where('user_id', $user->id)
                ->value('role') ?? $user->role;

            if (in_array($pivotRole, ['teknisi', 'kolektor'])) {
                return redirect()->intended(route('mobile.home'));
            }
            return redirect()->intended(route('dashboard'));
        }

        // If no business exists yet, redirect to business index or dashboard
        if (\App\Models\Business::count() === 0) {
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->route('admin.business.index');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
