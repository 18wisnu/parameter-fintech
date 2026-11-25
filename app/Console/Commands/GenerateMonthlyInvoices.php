<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Services\Billing\InvoiceGenerator;
use App\Services\Mixradius\MixradiusException;
use Illuminate\Console\Command;

/** Dijalankan otomatis setiap tanggal 1 (routes/console.php). */
class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'billing:generate-invoices {business? : ID unit bisnis (kosong = semua)}';

    protected $description = 'Buat tagihan bulan berjalan; pelanggan yang sudah bayar di server tidak ditagih';

    public function handle(): int
    {
        $query = Business::query();
        if ($id = $this->argument('business')) {
            $query->whereKey($id);
        }

        $failed = false;
        foreach ($query->get() as $business) {
            if (!$business->feature('invoices') || !$business->isServiceable()) {
                continue;
            }
            try {
                $s = (new InvoiceGenerator($business))->generate();
                $this->info("[{$business->name}] {$s['created']} tagihan dibuat, {$s['covered']} sudah bayar di server, {$s['existing']} sudah ada, {$s['voided']} ditandai lunas di server.");
            } catch (MixradiusException $e) {
                $failed = true;
                $this->error("[{$business->name}] Dibatalkan, data server jaringan tidak bisa diambil: {$e->getMessage()}");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
