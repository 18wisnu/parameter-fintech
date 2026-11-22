<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\Mixradius\ServiceRenewal;
use Illuminate\Console\Command;

class RetryServiceRenewals extends Command
{
    protected $signature = 'mixradius:retry-renewals';

    protected $description = 'Coba ulang perpanjangan layanan internet untuk tagihan lunas yang gagal/tertahan';

    public function handle(): int
    {
        $invoices = Invoice::withoutGlobalScope('tenant')
            ->where('status', 'paid')
            ->where('renewal_attempts', '<', ServiceRenewal::MAX_ATTEMPTS)
            ->where(fn ($q) => $q->where('renewal_status', 'failed')
                // pending tertahan (mis. proses terputus di tengah request)
                ->orWhere(fn ($q) => $q->where('renewal_status', 'pending')->where('updated_at', '<', now()->subMinutes(10))))
            ->orderBy('id')
            ->limit(50)
            ->get();

        $renewal = new ServiceRenewal();
        foreach ($invoices as $invoice) {
            $result = $renewal->attempt($invoice);
            $this->line("{$invoice->invoice_number}: {$result->renewal_status}" . ($result->renewal_error ? " — {$result->renewal_error}" : ''));
        }

        $this->info("{$invoices->count()} tagihan diproses.");

        return self::SUCCESS;
    }
}
