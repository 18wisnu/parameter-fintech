<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi rute berdasarkan role user DI TENANT AKTIF (pivot business_user).
 * Pemakaian: ->middleware('tenant.role:owner,admin')
 */
class EnsureTenantRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->current_business_id) {
            return redirect()->route('admin.business.index');
        }

        $role = $user->roleIn($user->current_business_id);

        if (in_array($role, $roles, true)) {
            return $next($request);
        }

        // Staf lapangan diarahkan ke aplikasi mobile
        if (in_array($role, ['teknisi', 'kolektor'], true) && !$request->expectsJson()) {
            return redirect()->route('mobile.home')
                ->with('error', 'Menu tersebut hanya untuk ' . implode('/', $roles) . ' di unit bisnis ini.');
        }

        abort(403, 'Anda tidak memiliki akses ke menu ini di unit bisnis yang aktif.');
    }
}
