<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\LogActivity::class,
            \App\Http\Middleware\CheckActiveBusiness::class,
            \App\Http\Middleware\EnsureSubscriptionActive::class,
        ]);
        $middleware->alias([
            'superadmin' => \App\Http\Middleware\EnsureSuperadmin::class,
            'tenant.role' => \App\Http\Middleware\EnsureTenantRole::class,
            'network' => \App\Http\Middleware\EnsureNetworkEnabled::class,
        ]);
        // Tambahkan baris ini agar Laravel percaya semua Proxy/Tunnel
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Bulan sudah tutup buku: kembali dengan pesan
        $exceptions->render(function (\App\Exceptions\ClosedPeriodException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 423);
            }

            return back()->withInput()->with('error', $e->getMessage());
        });

        // Error dari server jaringan: halaman GET tampilkan pesan, aksi POST/PUT kembali dengan pesan
        $exceptions->render(function (\App\Services\Mixradius\MixradiusException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 502);
            }
            if ($request->isMethod('GET')) {
                return response()->view('network.error', ['message' => $e->getMessage()], 502);
            }

            return back()->withInput()->with('error', $e->getMessage());
        });
    })->create();
    
