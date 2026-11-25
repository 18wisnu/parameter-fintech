<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Services\Mixradius\CustomerSync;
use App\Services\Mixradius\MixradiusException;
use Illuminate\Console\Command;

class MixradiusSync extends Command
{
    protected $signature = 'mixradius:sync {business? : ID unit bisnis (kosong = semua yang integrasinya aktif)}';

    protected $description = 'Tarik pelanggan & layanan dari Mixradius Evo ke setiap tenant';

    public function handle(): int
    {
        $query = Business::query()->where('mixradius_enabled', true)->whereNotNull('mixradius_base_url');
        if ($id = $this->argument('business')) {
            $query->whereKey($id);
        }

        $failed = false;
        foreach ($query->get() as $business) {
            if (!$business->isServiceable()) {
                $this->line("[{$business->name}] Dilewati: langganan NetBill tidak aktif.");
                continue;
            }
            try {
                $s = (new CustomerSync($business))->run();
                $this->info("[{$business->name}] {$s['total']} pelanggan: {$s['created']} baru, {$s['updated']} diperbarui ({$s['linked']} dihubungkan ke data lama), {$s['voided']} tagihan lunas di server.");
            } catch (MixradiusException $e) {
                $failed = true;
                $this->error("[{$business->name}] Gagal: {$e->getMessage()}");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
