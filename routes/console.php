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
