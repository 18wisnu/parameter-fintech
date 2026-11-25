<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Langganan usaha aktif berakhir / ditangguhkan → semua menu terkunci kecuali
 * halaman langganan (untuk memperpanjang), profil, ganti usaha, keluar & panel platform.
 * Data tidak dihapus; terbuka lagi begitu langganan diperpanjang.
 */
class EnsureSubscriptionActive
{
    private const ALLOWED = ['subscription.*', 'profile.*', 'admin.business.*', 'platform.*', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->current_business_id || $request->routeIs(...self::ALLOWED) || $request->is('logout')) {
            return $next($request);
        }

        $business = Business::find($user->current_business_id);
        if (!$business || $business->isServiceable()) {
            return $next($request);
        }

        $message = $business->subscriptionStatus() === 'suspended'
            ? 'Akun usaha ini ditangguhkan. Hubungi pengelola NetBill.'
            : 'Langganan NetBill usaha ini sudah berakhir. Perpanjang untuk membuka kembali semua menu.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 402);
        }

        return redirect()->route('subscription.index')->with('error', $message);
    }
}
