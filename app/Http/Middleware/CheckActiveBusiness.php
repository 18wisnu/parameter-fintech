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
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Validasi tenant aktif: harus masih terdaftar di pivot (bisa saja sudah dikeluarkan),
        // dan users.role disamakan dengan role di tenant aktif.
        if ($user->ensureCurrentBusiness()) {
            return $next($request);
        }

        // User belum terhubung ke tenant manapun (mis. baru daftar): arahkan untuk membuat/memilih bisnis
        if (\App\Models\Business::count() > 0) {
            if ($request->routeIs('admin.business.*', 'platform.*', 'profile.*') || $request->is('logout')) {
                return $next($request);
            }

            return redirect()->route('admin.business.index');
        }

        return $next($request);
    }
}
