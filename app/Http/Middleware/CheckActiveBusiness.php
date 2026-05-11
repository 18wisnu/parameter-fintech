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
            
            // Allow access to selection, logout, and profile
            if ($request->routeIs('admin.business.*') || $request->is('logout') || $request->routeIs('profile.*')) {
                return $next($request);
            }

            return redirect()->route('admin.business.index');
        }

        return $next($request);
    }
}
