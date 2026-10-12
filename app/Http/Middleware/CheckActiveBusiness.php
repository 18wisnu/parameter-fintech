<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckActiveBusiness
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If logged in but hasn't selected a business
        if (Auth::check() && !$user->current_business_id) {
            $business = $user->businesses()->first();
            if (!$business) {
                $business = \App\Models\Business::where('owner_id', $user->id)->first();
                if ($business) {
                    $user->businesses()->syncWithoutDetaching([$business->id => ['role' => 'owner']]);
                }
            }

            // Fallback: if there's any business in system and user is owner
            if (!$business && ($user->role === 'owner' || $user->role === 'admin')) {
                $business = \App\Models\Business::first();
                if ($business) {
                    $user->businesses()->syncWithoutDetaching([$business->id => ['role' => $user->role]]);
                }
            }

            if ($business) {
                $user->update(['current_business_id' => $business->id]);
                return $next($request);
            }

            // If still no business, only redirect if businesses exist in the DB
            if (\App\Models\Business::count() > 0) {
                // Allow access to selection, logout, and profile
                if ($request->routeIs('admin.business.*') || $request->is('logout') || $request->routeIs('profile.*')) {
                    return $next($request);
                }

                return redirect()->route('admin.business.index');
            }
        }

        return $next($request);
    }
}
