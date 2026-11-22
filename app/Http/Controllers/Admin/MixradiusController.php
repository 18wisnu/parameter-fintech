<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\Mixradius\CustomerSync;
use App\Services\Mixradius\MixradiusClient;
use App\Services\Mixradius\MixradiusException;
use Illuminate\Http\Request;

/** Pengaturan koneksi Mixradius Evo untuk tenant aktif (khusus owner). */
class MixradiusController extends Controller
{
    private function business(): Business
    {
        return Business::findOrFail(auth()->user()->current_business_id);
    }

    public function edit()
    {
        $business = $this->business();

        return view('admin.integrations.mixradius', [
            'business' => $business,
            'hasKey' => filled($business->mixradius_api_key),
            'hasSecret' => filled($business->mixradius_api_secret),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mixradius_enabled' => 'nullable|boolean',
            'mixradius_base_url' => 'nullable|url:http,https|max:255|required_if:mixradius_enabled,1',
            'mixradius_api_key' => 'nullable|string|max:1000',
            'mixradius_api_secret' => 'nullable|string|max:1000',
        ]);

        $business = $this->business();
        $business->mixradius_enabled = $request->boolean('mixradius_enabled');
        $business->mixradius_base_url = filled($data['mixradius_base_url'] ?? null)
            ? MixradiusClient::normalizeBaseUrl($data['mixradius_base_url'])
            : null;

        // Kosong = tidak diubah (nilai lama tidak pernah ditampilkan kembali di form)
        if ($request->filled('mixradius_api_key')) {
            $business->mixradius_api_key = $data['mixradius_api_key'];
        }
        if ($request->filled('mixradius_api_secret')) {
            $business->mixradius_api_secret = $data['mixradius_api_secret'];
        }

        $business->save();

        return redirect()->route('admin.mixradius.edit')->with('success', 'Pengaturan Mixradius disimpan.');
    }

    public function test()
    {
        try {
            $result = MixradiusClient::forBusiness($this->business())->testConnection();
        } catch (MixradiusException $e) {
            return redirect()->route('admin.mixradius.edit')->with('error', $e->getMessage());
        }

        $products = $result['products_total'] !== null ? " {$result['products_total']} produk terbaca." : '';

        return redirect()->route('admin.mixradius.edit')
            ->with('success', "Terhubung ke Mixradius, API key valid.{$products}");
    }

    /** Tarik pelanggan & layanan dari Mixradius ke tenant aktif. */
    public function sync()
    {
        @set_time_limit(300);

        try {
            $s = (new CustomerSync($this->business()))->run();
        } catch (MixradiusException $e) {
            return redirect()->route('admin.mixradius.edit')->with('error', 'Sinkronisasi gagal: ' . $e->getMessage());
        }

        return redirect()->route('admin.mixradius.edit')->with('success',
            "Sinkronisasi selesai: {$s['total']} pelanggan — {$s['created']} baru, {$s['updated']} diperbarui"
            . ($s['linked'] ? " ({$s['linked']} dihubungkan ke data lama berdasarkan kode pelanggan)" : '')
            . ($s['voided'] ? ". {$s['voided']} tagihan ternyata sudah dibayar di server, ditandai lunas di server" : '') . '.');
    }
}
