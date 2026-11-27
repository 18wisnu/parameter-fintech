<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sinkron pelanggan Mixradius tiap jam untuk tenant yang integrasinya aktif.
// Butuh cron di server: * * * * * php /path/artisan schedule:run
if (config('mixradius.auto_sync')) {
    \Illuminate\Support\Facades\Schedule::command('mixradius:sync')->hourly()->withoutOverlapping();
}

// Coba ulang perpanjangan layanan yang gagal setelah tagihan lunas
\Illuminate\Support\Facades\Schedule::command('mixradius:retry-renewals')->everyTenMinutes()->withoutOverlapping();

// Tagihan bulanan dibuat otomatis setiap tanggal 1 (setelah sinkron masa aktif dari server).
// Jika gagal (server tidak bisa dihubungi) dicoba lagi tiap jam sampai jam 23 di tanggal 1.
\Illuminate\Support\Facades\Schedule::command('billing:generate-invoices')
    ->monthlyOn(1, '00:15')->withoutOverlapping();
\Illuminate\Support\Facades\Schedule::command('billing:generate-invoices')
    ->hourly()->when(fn () => now()->day === 1 && now()->hour > 0)->withoutOverlapping();

// Usaha yang diminta dihapus: hapus permanen setelah masa tunggu 30 hari lewat
\Illuminate\Support\Facades\Schedule::command('netbill:purge-businesses')->dailyAt('02:30')->withoutOverlapping();
