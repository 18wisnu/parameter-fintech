<?php

namespace App\Services\Mixradius;

use App\Models\Business;
use App\Models\Customer;
use Illuminate\Support\Facades\Cache;

/**
 * Server jaringan tidak menyebut jenis sesi online. Jenisnya ditentukan dari username:
 *  - username layanan PPP            → PPPoE
 *  - username layanan hotspot        → Hotspot berlangganan
 *  - username layanan DHCP / static  → Lainnya
 *  - selain itu (kode voucher)       → Voucher hotspot
 */
class SessionKinds
{
    public const LABELS = [
        'pppoe' => 'PPPoE',
        'hotspot' => 'Hotspot langganan',
        'voucher' => 'Voucher hotspot',
        'other' => 'DHCP / Static',
    ];

    /** username => ['kind' => ..., 'customer_id' => ?int, 'customer_name' => ?string] */
    public static function map(Business $business, ?MixradiusClient $client = null): array
    {
        // Daftar layanan jarang berubah: cache 10 menit (hemat batas 60 permintaan/menit)
        $services = Cache::remember("network:{$business->id}:service-kinds", 600, function () use ($business, $client) {
            $client ??= MixradiusClient::forBusiness($business);
            $map = [];
            foreach ($client->paginate('/v1/services') as $s) {
                if (!empty($s['username'])) {
                    $map[$s['username']] = ['type' => $s['service_type'] ?? null, 'code' => $s['customer_code'] ?? null];
                }
            }

            return $map;
        });

        $locals = Customer::withoutGlobalScope('tenant')->where('business_id', $business->id)
            ->whereNotNull('code')->whereIn('code', collect($services)->pluck('code')->filter()->unique())
            ->get(['id', 'name', 'code'])->keyBy('code');

        $out = [];
        foreach ($services as $username => $s) {
            $local = $s['code'] ? ($locals[$s['code']] ?? null) : null;
            $out[$username] = [
                'kind' => match ($s['type']) { 'ppp' => 'pppoe', 'hotspot' => 'hotspot', default => 'other' },
                'customer_id' => $local?->id,
                'customer_name' => $local?->name,
            ];
        }

        return $out;
    }

    public static function of(array $map, ?string $username): array
    {
        return $map[(string) $username] ?? ['kind' => 'voucher', 'customer_id' => null, 'customer_name' => null];
    }
}
