<?php

/*
|--------------------------------------------------------------------------
| Integrasi Mixradius Evo (MixRadius REST API v1.2)
|--------------------------------------------------------------------------
| URL panel, API key & secret diisi PER TENANT di menu "Integrasi Mixradius"
| (tabel businesses). File ini berisi hal yang sama untuk semua tenant.
|
| Referensi: {panel}/docs/api-reference/ — bagian "Server API".
| Auth server-to-server: header X-API-Key + X-API-Secret (dibuat admin ISP,
| izin per key, mis. customers.read, services.read).
*/

return [

    'timeout' => (int) env('MIXRADIUS_TIMEOUT', 20),

    // Verifikasi sertifikat SSL. Set false hanya jika panel memakai self-signed cert.
    'verify_ssl' => (bool) env('MIXRADIUS_VERIFY_SSL', true),

    // Semua endpoint ada di bawah {base_url}/api
    'api_prefix' => '/api',

    'auth' => [
        'key_header' => 'X-API-Key',
        'secret_header' => 'X-API-Secret',
    ],

    // Maks 100 per dokumentasi
    'per_page' => 100,

    // Limit umum 60 request/menit. Jika kena 429, tunggu lalu coba lagi.
    'retry_on_rate_limit' => [
        'times' => 3,
        'sleep_ms' => 2000,
    ],

    // Sinkronisasi otomatis semua tenant yang integrasinya aktif (butuh cron `schedule:run`)
    'auto_sync' => (bool) env('MIXRADIUS_AUTO_SYNC', true),
];
