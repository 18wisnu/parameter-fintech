<?php

return [
    // Merek produk & badan usaha pemilik (tampil di footer semua halaman)
    'name' => 'NetBill',
    'tagline' => 'Billing untuk usaha internet kecil-kecilan',
    'company' => env('NETBILL_COMPANY', 'CV. Parameter Digital Inovation'),

    // Masa coba untuk usaha yang baru mendaftar (hari)
    'trial_days' => (int) env('NETBILL_TRIAL_DAYS', 14),

    // Pengingat perpanjangan muncul N hari sebelum langganan berakhir
    'reminder_days' => 7,
];
