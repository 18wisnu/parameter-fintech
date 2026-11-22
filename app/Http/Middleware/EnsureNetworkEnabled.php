<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Menu Jaringan hanya untuk tenant yang sudah terhubung ke server jaringan. */
class EnsureNetworkEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $business = Business::find($request->user()?->current_business_id);

        if (!$business || !$business->hasMixradius()) {
            $owner = $business && (int) $business->owner_id === (int) $request->user()->id;

            return redirect()->route($owner ? 'admin.mixradius.edit' : 'dashboard')
                ->with('error', 'Fitur jaringan belum aktif untuk unit bisnis ini.'
                    . ($owner ? ' Hubungkan server jaringan terlebih dahulu.' : ' Hubungi pemilik unit bisnis.'));
        }

        return $next($request);
    }
}
