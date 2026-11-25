<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Panel pengelola NetBill: hanya untuk akun superadmin. Selain itu dianggap tidak ada (404). */
class EnsureSuperadmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->is_superadmin, 404);

        return $next($request);
    }
}
