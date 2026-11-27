<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Services\BusinessDataEraser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/** Hapus permanen usaha yang masa tunggu penghapusannya sudah lewat. */
class PurgeDeletedBusinesses extends Command
{
    protected $signature = 'netbill:purge-businesses';

    protected $description = 'Hapus permanen usaha yang dijadwalkan dihapus dan masa tunggunya sudah lewat';

    public function handle(): int
    {
        $due = Business::whereNotNull('deletion_scheduled_at')->where('deletion_scheduled_at', '<=', now())->get();

        foreach ($due as $business) {
            BusinessDataEraser::purge($business);
            Log::info("Usaha #{$business->id} ({$business->name}) dihapus permanen setelah masa tunggu.");
            $this->info("Dihapus: #{$business->id} {$business->name}");
        }

        if ($due->isEmpty()) {
            $this->info('Tidak ada usaha yang perlu dihapus.');
        }

        return self::SUCCESS;
    }
}
