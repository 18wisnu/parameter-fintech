<?php

namespace App\Services\Mixradius;

use App\Models\Business;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Penghubung ke MixRadius REST API v1.2 (Server API) milik satu tenant.
 * Auth: X-API-Key + X-API-Secret. Response: { success, message, data }.
 *
 *   $mr = MixradiusClient::forBusiness($business);
 *   foreach ($mr->paginate('/v1/customers') as $customer) { ... }
 *   $mr->setCustomerStatus(1, 'suspended');
 */
class MixradiusClient
{
    private string $baseUrl;

    public function __construct(
        string $baseUrl,
        private ?string $apiKey,
        private ?string $apiSecret,
    ) {
        $this->baseUrl = self::normalizeBaseUrl($baseUrl);
    }

    public static function forBusiness(Business $business): self
    {
        if (!$business->mixradius_base_url) {
            throw new MixradiusException('URL server jaringan belum diisi untuk unit bisnis ini.');
        }
        if (!$business->mixradius_api_key || !$business->mixradius_api_secret) {
            throw new MixradiusException('Kunci API server jaringan belum diisi untuk unit bisnis ini.');
        }

        return new self(
            $business->mixradius_base_url,
            $business->mixradius_api_key,
            $business->mixradius_api_secret,
        );
    }

    /** Terima "https://x.mixradius.com", ".../api" atau ".../api/v1" → "https://x.mixradius.com" */
    public static function normalizeBaseUrl(string $url): string
    {
        $url = rtrim(trim($url), '/');

        return preg_replace('#/api(/v1)?$#i', '', $url);
    }

    // ------------------------------------------------------------------
    // Endpoint yang dipakai aplikasi
    // ------------------------------------------------------------------

    /**
     * Cek URL + kredensial. Memakai katalog produk karena endpoint ini
     * butuh API key valid tapi tidak butuh izin khusus.
     */
    public function testConnection(): array
    {
        $data = $this->get('/v1/products/catalog', ['per_page' => 1]);

        return ['products_total' => $data['total'] ?? null];
    }

    /** Katalog paket untuk tenant (cache 10 menit). type: ppp, hotspot, dhcp, static, atau null = semua. */
    public static function cachedCatalog(Business $business, ?string $type = null): array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "network:{$business->id}:products:" . ($type ?? 'all'),
            600,
            fn () => iterator_to_array(self::forBusiness($business)->paginate('/v1/products/catalog', array_filter(['type' => $type])), false)
        );
    }

    /** PUT /v1/customers/{id}/status — active, inactive, suspended, terminated (butuh customers.write) */
    public function setCustomerStatus(int|string $mixradiusCustomerId, string $status): array
    {
        return $this->put("/v1/customers/{$mixradiusCustomerId}/status", ['status' => $status]);
    }

    /** POST /v1/services/{id}/renew — bayar + perpanjang + sync RADIUS (butuh payments.write) */
    public function renewService(int|string $serviceId, array $payload = []): array
    {
        return $this->post("/v1/services/{$serviceId}/renew", $payload);
    }

    /** POST /v1/radius/disconnect/{username} (butuh radius.disconnect) */
    public function disconnect(string $username): array
    {
        return $this->post('/v1/radius/disconnect/' . rawurlencode($username));
    }

    // ------------------------------------------------------------------
    // HTTP dasar
    // ------------------------------------------------------------------

    /** Ambil semua item dari endpoint list berpaginasi (data.items + data.total_pages). */
    public function paginate(string $path, array $query = []): Generator
    {
        $page = 1;
        do {
            $data = $this->get($path, $query + ['page' => $page, 'per_page' => config('mixradius.per_page', 100)]);

            foreach ($data['items'] ?? [] as $item) {
                yield $item;
            }

            $totalPages = (int) ($data['total_pages'] ?? 1);
            $page++;
        } while ($page <= $totalPages);
    }

    /** Mengembalikan isi `data` dari response. */
    public function get(string $path, array $query = []): array
    {
        return $this->data($this->send('get', $path, $query));
    }

    public function post(string $path, array $body = []): array
    {
        return $this->data($this->send('post', $path, $body));
    }

    public function put(string $path, array $body = []): array
    {
        return $this->data($this->send('put', $path, $body));
    }

    private function send(string $method, string $path, array $payload = []): Response
    {
        $url = $this->baseUrl . config('mixradius.api_prefix', '/api') . '/' . ltrim($path, '/');

        try {
            $response = $this->request()->{$method}($url, $payload);
        } catch (ConnectionException $e) {
            throw new MixradiusException('Tidak bisa terhubung ke server jaringan: ' . $e->getMessage(), 0, $e);
        }

        if ($response->successful()) {
            return $response;
        }

        $message = $response->json('message') ?: mb_strimwidth((string) $response->body(), 0, 200, '…');

        throw new MixradiusException(match ($response->status()) {
            401 => 'Kunci API server jaringan tidak valid (401). Periksa menu Integrasi.',
            402 => "Saldo tidak cukup (402): {$message}",
            403 => "Kunci API tidak punya izin untuk {$path} (403). Tambahkan izinnya di pengaturan API server jaringan. {$message}",
            404 => "Data tidak ditemukan di server jaringan (404). {$message}",
            422 => "Server jaringan menolak data (422): {$message}",
            429 => 'Terlalu banyak permintaan ke server jaringan (429). Coba lagi sebentar lagi.',
            default => "Server jaringan error HTTP {$response->status()}: {$message}",
        }, $response->status());
    }

    private function request(): PendingRequest
    {
        $retry = config('mixradius.retry_on_rate_limit');

        return Http::acceptJson()
            ->asJson()
            ->timeout(config('mixradius.timeout', 20))
            ->withOptions(['verify' => config('mixradius.verify_ssl', true)])
            ->withHeaders([
                config('mixradius.auth.key_header', 'X-API-Key') => (string) $this->apiKey,
                config('mixradius.auth.secret_header', 'X-API-Secret') => (string) $this->apiSecret,
            ])
            // Hanya ulangi jika kena rate limit (429)
            ->retry($retry['times'] ?? 3, $retry['sleep_ms'] ?? 2000,
                fn ($e) => $e instanceof \Illuminate\Http\Client\RequestException && $e->response->status() === 429,
                throw: false);
    }

    private function data(Response $response): array
    {
        $json = $response->json();

        if (!is_array($json)) {
            throw new MixradiusException('Respons server jaringan tidak valid. Pastikan URL server benar.');
        }
        if (($json['success'] ?? true) === false) {
            throw new MixradiusException('Server jaringan: ' . ($json['message'] ?? 'permintaan gagal.'));
        }

        return is_array($json['data'] ?? null) ? $json['data'] : $json;
    }
}
