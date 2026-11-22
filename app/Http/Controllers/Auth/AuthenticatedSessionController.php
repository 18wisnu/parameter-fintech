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

        // Pakai tenant terakhir (jika masih terdaftar), atau tenant pertama tempat email ini terdaftar
        $user = Auth::user();

        if ($user->ensureCurrentBusiness()) {
            $pivotRole = $user->roleIn($user->current_business_id) ?? $user->role;

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
