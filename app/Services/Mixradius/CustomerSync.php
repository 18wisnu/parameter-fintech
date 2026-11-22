<?php

namespace App\Services\Mixradius;

use App\Models\Business;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

/**
 * Tarik pelanggan + layanan dari Mixradius ke tabel customers milik SATU tenant.
 * Mixradius adalah sumber data: nama, kontak, status, paket & jatuh tempo ditimpa
 * dari Mixradius. Data lokal lain (petugas aktivasi, tagihan, setoran) tidak disentuh.
 *
 * Butuh izin API key: customers.read & services.read.
 */
class CustomerSync
{
    public function __construct(private Business $business, private ?MixradiusClient $client = null)
    {
        $this->client ??= MixradiusClient::forBusiness($business);
    }

    /** @return array{created:int, updated:int, linked:int, total:int, voided:int} */
    public function run(): array
    {
        // 1. Layanan dikelompokkan per kode pelanggan
        $servicesByCode = [];
        foreach ($this->client->paginate('/v1/services') as $service) {
            if (!empty($service['customer_code'])) {
                $servicesByCode[$service['customer_code']][] = $service;
            }
        }

        $stats = ['created' => 0, 'updated' => 0, 'linked' => 0, 'total' => 0];
        $now = now();

        // 2. Pelanggan
        foreach ($this->client->paginate('/v1/customers') as $remote) {
            if (!isset($remote['id'])) {
                continue;
            }
            $stats['total']++;

            $service = $this->primaryService($servicesByCode[$remote['customer_code'] ?? ''] ?? []);
            $attributes = $this->mapAttributes($remote, $service) + ['mixradius_synced_at' => $now];

            DB::transaction(function () use ($remote, $attributes, &$stats) {
                $query = Customer::withoutGlobalScope('tenant')->where('business_id', $this->business->id);

                $customer = (clone $query)->where('mixradius_id', (string) $remote['id'])->first();

                // Pelanggan lokal lama dengan kode yang sama: hubungkan, jangan buat duplikat
                if (!$customer && !empty($remote['customer_code'])) {
                    $customer = (clone $query)->whereNull('mixradius_id')
                        ->where(fn ($q) => $q->where('code', $remote['customer_code'])
                            ->orWhere('customer_id', $remote['customer_code']))
                        ->first();
                    if ($customer) {
                        $stats['linked']++;
                    }
                }

                if ($customer) {
                    // ID Pelanggan lokal yang sudah diisi tidak ditimpa
                    if ($customer->customer_id) {
                        unset($attributes['customer_id']);
                    }
                    $customer->fill($attributes)->save();
                    $stats['updated']++;
                } else {
                    $customer = new Customer($attributes);
                    $customer->business_id = $this->business->id;
                    $customer->save();
                    $stats['created']++;
                }
            });
        }

        $this->business->forceFill(['mixradius_last_sync_at' => $now])->save();

        // Tagihan yang ternyata sudah dibayar di server tidak lagi dihitung belum bayar
        $stats['voided'] = (new \App\Services\Billing\InvoiceGenerator($this->business))->reconcile();

        return $stats;
    }

    /** Layanan utama: yang aktif lebih dulu, lalu yang pertama. */
    private function primaryService(array $services): ?array
    {
        foreach ($services as $s) {
            if (($s['status'] ?? null) === 'active') {
                return $s;
            }
        }

        return $services[0] ?? null;
    }

    private function mapAttributes(array $remote, ?array $service): array
    {
        $accountStatus = $remote['status'] ?? 'active';
        $serviceStatus = $service['status'] ?? null;

        $isolated = $accountStatus === 'suspended' || in_array($serviceStatus, ['suspended', 'expired'], true);

        $status = match (true) {
            in_array($accountStatus, ['inactive', 'terminated'], true) => 'inactive',
            $isolated => 'isolated',
            default => 'active',
        };

        $attributes = [
            'mixradius_id' => (string) $remote['id'],
            'code' => $remote['customer_code'] ?? null,
            'customer_id' => $remote['customer_code'] ?? null,
            'name' => $remote['fullname'] ?? ($remote['customer_code'] ?? 'Tanpa Nama'),
            'phone' => $remote['phone'] ?? null,
            'email' => $remote['email'] ?? null,
            'address' => $remote['address'] ?? null,
            'latitude' => isset($remote['latitude']) && $remote['latitude'] !== '' ? (float) $remote['latitude'] : null,
            'longitude' => isset($remote['longitude']) && $remote['longitude'] !== '' ? (float) $remote['longitude'] : null,
            'entity_name' => $remote['entity']['name'] ?? null,
            'status' => $status,
            'is_isolated' => $isolated,
        ];

        if ($service) {
            $attributes += [
                'type' => match ($service['service_type'] ?? null) {
                    'ppp' => 'pppoe',
                    'hotspot' => 'hotspot',
                    default => 'general',
                },
                'package_fee' => (float) ($service['product']['price'] ?? $service['price'] ?? 0),
                'product_name' => $service['product']['name'] ?? $service['product_name'] ?? null,
                'bandwidth' => $service['product']['bandwidth'] ?? $service['bandwidth_name'] ?? null,
                'billing_type' => $service['billing_type'] ?? null,
                'billing_cycle' => $service['billing_cycle'] ?? null,
                'due_date' => $service['expiry_date'] ?? null,
                'mixradius_service_id' => isset($service['service_id']) ? (string) $service['service_id'] : null,
                'mixradius_username' => $service['username'] ?? null,
            ];
        }

        return $attributes;
    }
}
