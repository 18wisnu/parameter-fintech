<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\Mixradius\MixradiusClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Dasar semua menu Jaringan. Data diambil langsung dari server jaringan (Mixradius)
 * milik tenant aktif; nama Mixradius tidak pernah ditampilkan ke pengguna.
 * Error dari server ditangani terpusat di bootstrap/app.php (MixradiusException).
 */
abstract class NetworkController extends Controller
{
    protected function business(): Business
    {
        return Business::findOrFail(auth()->user()->current_business_id);
    }

    protected function client(): MixradiusClient
    {
        return MixradiusClient::forBusiness($this->business());
    }

    /**
     * Satu halaman data dari endpoint list. Filter kosong dibuang.
     * @return array{items: array, page: int, total: int, total_pages: int}
     */
    protected function remotePage(string $path, Request $request, array $filters = [], int $perPage = 20): array
    {
        $query = array_filter(
            $request->only($filters),
            fn ($v) => $v !== null && $v !== ''
        );

        $data = $this->client()->get($path, $query + [
            'page' => max(1, (int) $request->query('page', 1)),
            'per_page' => $perPage,
        ]);

        return [
            'items' => $data['items'] ?? [],
            'page' => (int) ($data['page'] ?? 1),
            'total' => (int) ($data['total'] ?? 0),
            'total_pages' => max(1, (int) ($data['total_pages'] ?? 1)),
        ];
    }

    /** Katalog produk (cache 10 menit per tenant). */
    protected function products(?string $type = null): array
    {
        return MixradiusClient::cachedCatalog($this->business(), $type);
    }
}
