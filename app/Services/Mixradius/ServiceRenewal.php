<?php

namespace App\Services\Mixradius;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Setelah tagihan LUNAS di aplikasi ini, perpanjang layanan pelanggan di Mixradius
 * (POST /v1/services/{id}/renew: tandai lunas di Mixradius + perpanjang masa aktif +
 * sinkron RADIUS, sehingga isolir otomatis terbuka).
 *
 * Aplikasi ini adalah billing utama; Mixradius hanya mesin RADIUS di belakang layar.
 *
 * Aman diulang: endpoint renew Mixradius tidak idempoten, jadi sebelum mencoba ulang
 * kita cek dulu masa aktif layanan. Jika sudah lebih panjang dari sebelum percobaan
 * pertama, artinya perpanjangan sebelumnya sebenarnya berhasil (mis. timeout) → tidak
 * diperpanjang dua kali.
 */
class ServiceRenewal
{
    public const MAX_ATTEMPTS = 5;

    /** @var callable|null untuk tes */
    public static $clientFactory = null;

    /**
     * Tandai tagihan perlu diperpanjang & langsung coba. Tidak pernah melempar exception:
     * pembayaran lokal sudah tercatat, kegagalan dicatat di invoice untuk dicoba ulang.
     */
    public static function afterPaid(Invoice $invoice, string $paymentMethod = 'cash', ?string $note = null): Invoice
    {
        $customer = self::customerOf($invoice);
        $business = $customer ? Business::find($customer->business_id) : null;

        if (!$customer || !$business || !$business->hasMixradius() || !$customer->mixradius_service_id) {
            return $invoice; // Pelanggan tidak terhubung ke server: tidak ada yang perlu diperpanjang
        }

        if (in_array($invoice->renewal_status, ['pending', 'success'], true)) {
            return $invoice;
        }

        $invoice->forceFill([
            'renewal_status' => 'pending',
            'renewal_payment_method' => $paymentMethod,
            'renewal_note' => $note ? mb_strimwidth($note, 0, 250) : null,
        ])->save();

        return (new self())->attempt($invoice);
    }

    public function attempt(Invoice $invoice): Invoice
    {
        if ($invoice->renewal_status === 'success') {
            return $invoice;
        }

        $customer = self::customerOf($invoice);
        $business = $customer ? Business::find($customer->business_id) : null;

        if (!$customer || !$business || !$business->hasMixradius() || !$customer->mixradius_service_id) {
            return $this->fail($invoice, 'Pelanggan tidak lagi terhubung ke server layanan.');
        }

        try {
            $client = self::$clientFactory ? (self::$clientFactory)($business) : MixradiusClient::forBusiness($business);

            $currentExpiry = $this->currentExpiry($client, $customer);

            // Percobaan ulang: perpanjangan sebelumnya ternyata sudah masuk
            if ($invoice->renewal_attempts > 0 && $invoice->renewal_expiry_before && $currentExpiry
                && $currentExpiry->gt($invoice->renewal_expiry_before)) {
                return $this->succeed($invoice, $customer, $currentExpiry, null);
            }

            // Percobaan ulang tanpa bisa memastikan hasil sebelumnya: jangan tebak (bisa dobel perpanjang)
            if ($invoice->renewal_attempts > 0 && (!$invoice->renewal_expiry_before || !$currentExpiry)) {
                $invoice->renewal_attempts = self::MAX_ATTEMPTS;

                return $this->fail($invoice, 'Tidak bisa memastikan apakah perpanjangan sebelumnya sudah masuk. Cek masa aktif layanan pelanggan secara manual, lalu klik "Tandai Aktif" bila sudah diperpanjang.');
            }

            if ($invoice->renewal_attempts === 0) {
                $invoice->renewal_expiry_before = $currentExpiry;
            }
            $invoice->renewal_attempts++;
            $invoice->save();

            $result = $client->renewService($customer->mixradius_service_id, array_filter([
                'payment_method' => $invoice->renewal_payment_method ?: 'cash',
                'reference' => $invoice->invoice_number,
                'notes' => $invoice->renewal_note,
            ]));

            $newExpiry = !empty($result['new_expiry']) ? Carbon::parse($result['new_expiry']) : null;

            return $this->succeed($invoice, $customer, $newExpiry, $result['invoice_number'] ?? null);
        } catch (MixradiusException $e) {
            Log::warning('Perpanjangan layanan gagal', ['invoice' => $invoice->id, 'error' => $e->getMessage()]);

            return $this->fail($invoice, $e->getMessage());
        }
    }

    /** Masa aktif layanan saat ini menurut Mixradius (dicari lewat username, lalu dicocokkan service_id). */
    private function currentExpiry(MixradiusClient $client, Customer $customer): ?Carbon
    {
        $search = $customer->mixradius_username ?: ($customer->code ?: null);
        if (!$search) {
            return null;
        }

        foreach ($client->paginate('/v1/services', ['search' => $search]) as $service) {
            if ((string) ($service['service_id'] ?? '') === (string) $customer->mixradius_service_id) {
                return !empty($service['expiry_date']) ? Carbon::parse($service['expiry_date']) : null;
            }
        }

        return null;
    }

    /** Admin sudah memastikan sendiri layanan aktif (mis. setelah cek manual). */
    public static function markResolvedManually(Invoice $invoice): Invoice
    {
        $invoice->forceFill([
            'renewal_status' => 'success',
            'renewal_error' => 'Ditandai aktif manual oleh ' . (auth()->user()->name ?? 'sistem') . '.',
            'renewed_at' => now(),
        ])->save();

        return $invoice;
    }

    private function succeed(Invoice $invoice, Customer $customer, ?Carbon $newExpiry, ?string $remoteInvoice): Invoice
    {
        $invoice->forceFill([
            'renewal_status' => 'success',
            'renewal_error' => null,
            'renewal_new_expiry' => $newExpiry,
            'renewal_remote_invoice' => $remoteInvoice ?? $invoice->renewal_remote_invoice,
            'renewed_at' => now(),
        ])->save();

        $customer->forceFill(array_filter([
            'due_date' => $newExpiry?->toDateString(),
            'is_isolated' => false,
            'status' => $customer->status === 'isolated' ? 'active' : null,
        ], fn ($v) => $v !== null))->save();

        return $invoice;
    }

    private function fail(Invoice $invoice, string $message): Invoice
    {
        $invoice->forceFill([
            'renewal_status' => 'failed',
            'renewal_error' => mb_strimwidth($message, 0, 1000),
        ])->save();

        return $invoice;
    }

    private static function customerOf(Invoice $invoice): ?Customer
    {
        return Customer::withoutGlobalScope('tenant')->find($invoice->customer_id);
    }
}
