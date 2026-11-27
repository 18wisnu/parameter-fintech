<?php

namespace App\Services\Billing;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Services\Mixradius\CustomerSync;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Tagihan bulanan per tenant. Periode selalu tanggal 1.
 *
 * Pelanggan yang terhubung ke server jaringan dianggap SUDAH BAYAR periode itu bila
 * masa aktif layanannya (due_date, hasil sinkron) mencapai akhir bulan periode —
 * mis. sudah bayar langsung di server / payment gateway. Untuk mereka tidak dibuat
 * tagihan, dan tagihan lama yang masih "belum bayar" ditandai 'void' (lunas di server).
 */
class InvoiceGenerator
{
    public function __construct(private Business $business)
    {
    }

    /** @return array{created:int, covered:int, existing:int, voided:int, synced:bool} */
    public function generate(?Carbon $period = null, bool $sync = true): array
    {
        $period = ($period ?? now())->copy()->startOfMonth();
        $synced = false;

        // Data masa aktif harus terbaru sebelum memutuskan siapa yang ditagih.
        if ($sync && $this->business->hasMixradius()) {
            (new CustomerSync($this->business))->run(); // gagal → exception, tidak ada tagihan dibuat
            $synced = true;
        }

        $stats = ['created' => 0, 'covered' => 0, 'existing' => 0, 'voided' => 0, 'synced' => $synced];

        $customers = Customer::withoutGlobalScope('tenant')
            ->where('business_id', $this->business->id)
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'inactive'))
            ->where('package_fee', '>', 0)
            ->subscribers() // outlet voucher tidak ditagih bulanan
            ->get();

        foreach ($customers as $customer) {
            $exists = Invoice::withoutGlobalScope('tenant')
                ->where('customer_id', $customer->id)
                ->whereDate('period_date', $period->toDateString())
                ->exists();

            if ($exists) {
                $stats['existing']++;
                continue;
            }

            if (self::coversPeriod($customer, $period)) {
                $stats['covered']++;
                continue;
            }

            if ($this->createFor($customer, $period)) {
                $stats['created']++;
            }
        }

        $stats['voided'] = $this->reconcile();

        return $stats;
    }

    /**
     * Buat satu tagihan untuk pelanggan & periode (tanpa cek duplikat/masa aktif).
     * Null bila pelanggan belum mulai berlangganan atau total tagihan nol.
     */
    public function createFor(Customer $customer, Carbon $period): ?Invoice
    {
        $period = $period->copy()->startOfMonth();
        $calc = self::lines($customer, $period);
        if ($calc === null || $calc['total'] <= 0) {
            return null;
        }

        return DB::transaction(function () use ($customer, $period, $calc) {
            $invoice = new Invoice([
                'customer_id' => $customer->id,
                'invoice_number' => 'INV-' . $period->format('Ym') . '-' . str_pad((string) $customer->id, 4, '0', STR_PAD_LEFT),
                'period_date' => $period->toDateString(),
                'amount' => $calc['total'],
                'details' => ['lines' => $calc['lines'], 'prorata' => $calc['prorata']],
                'status' => 'unpaid',
            ]);
            $invoice->business_id = $this->business->id;
            $invoice->save();

            if ($calc['installation']) {
                $customer->update(['installation_billed' => true]);
            }

            return $invoice;
        });
    }

    /**
     * Rincian tagihan satu periode:
     *  - paket per bulan; prorata bila pelanggan mulai di tengah bulan periode
     *    (hanya pelanggan yang tidak terhubung server — masa aktif pelanggan server diatur server)
     *  - diskon bulanan
     *  - biaya pasang, sekali di tagihan pertama
     *
     * @return array{lines: array, total: float, prorata: bool, installation: bool}|null  null = belum mulai berlangganan
     */
    public static function lines(Customer $customer, Carbon $period): ?array
    {
        $period = $period->copy()->startOfMonth();
        $fee = (float) $customer->package_fee;
        $lines = [];
        $prorata = false;

        $start = $customer->start_date ? Carbon::parse($customer->start_date)->startOfDay() : null;
        if ($start && $start->gt($period->copy()->endOfMonth())) {
            return null;
        }

        $label = 'Paket internet' . ($customer->product_name ? ' — ' . $customer->product_name : '')
            . ' ' . $period->locale('id')->translatedFormat('F Y');

        if ($start && !$customer->isLinked() && $start->isSameMonth($period) && $start->day > 1) {
            $daysInMonth = $period->daysInMonth;
            $days = $daysInMonth - $start->day + 1;
            $amount = round($fee * $days / $daysInMonth / 1000) * 1000; // dibulatkan ke ribuan terdekat
            $lines[] = ['label' => sprintf('%s (prorata %d/%d hari, mulai %s)', $label, $days, $daysInMonth, $start->format('d/m')), 'amount' => $amount];
            $prorata = true;
            $packageAmount = $amount;
        } else {
            $lines[] = ['label' => $label, 'amount' => $fee];
            $packageAmount = $fee;
        }

        $discount = min((float) $customer->discount, $packageAmount);
        if ($discount > 0) {
            $lines[] = ['label' => 'Diskon', 'amount' => -$discount];
        }

        $installation = (float) $customer->installation_fee > 0 && !$customer->installation_billed;
        if ($installation) {
            $lines[] = ['label' => 'Biaya pemasangan', 'amount' => (float) $customer->installation_fee];
        }

        return [
            'lines' => $lines,
            'total' => max(0, array_sum(array_column($lines, 'amount'))),
            'prorata' => $prorata,
            'installation' => $installation,
        ];
    }

    /**
     * Tandai 'void' tagihan belum bayar milik pelanggan yang masa aktifnya di server
     * sudah mencakup periode tagihan (sudah dibayar di luar aplikasi).
     */
    public function reconcile(): int
    {
        $pendingInvoiceIds = Deposit::withoutGlobalScope('tenant')
            ->where('business_id', $this->business->id)
            ->where('status', 'pending')
            ->whereNotNull('invoice_id')
            ->pluck('invoice_id');

        $open = Invoice::withoutGlobalScope('tenant')
            ->where('business_id', $this->business->id)
            ->where('status', 'unpaid')
            ->whereNotIn('id', $pendingInvoiceIds)
            ->with(['customer' => fn ($q) => $q->withoutGlobalScope('tenant')])
            ->get();

        $voided = 0;
        foreach ($open as $invoice) {
            $customer = $invoice->customer;
            if (!$customer || !self::coversPeriod($customer, Carbon::parse($invoice->period_date))) {
                continue;
            }

            DB::transaction(function () use ($invoice, $customer) {
                $invoice->update([
                    'status' => 'void',
                    'void_reason' => 'Sudah dibayar di server jaringan — aktif s/d '
                        . Carbon::parse($customer->due_date)->locale('id')->translatedFormat('d M Y'),
                ]);
            });
            $voided++;
        }

        return $voided;
    }

    /** Masa aktif pelanggan (terhubung server) sudah sampai akhir bulan periode? */
    public static function coversPeriod(Customer $customer, Carbon $period): bool
    {
        if (!$customer->isLinked() || !$customer->due_date) {
            return false;
        }

        return Carbon::parse($customer->due_date)->startOfDay()
            ->greaterThanOrEqualTo($period->copy()->endOfMonth()->startOfDay());
    }
}
