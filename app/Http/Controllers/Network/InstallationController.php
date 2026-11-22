<?php

namespace App\Http\Controllers\Network;

use Illuminate\Http\Request;

/**
 * Pengajuan pemasangan baru. Masuk ke daftar instalasi server dengan status "new";
 * pelanggan & layanan dibuat saat instalasi diaktifkan, lalu ikut tersinkron ke aplikasi.
 * Izin: installations.read, installations.write.
 */
class InstallationController extends NetworkController
{
    public const STATUSES = ['new' => 'Baru', 'scheduled' => 'Terjadwal', 'in_progress' => 'Dikerjakan', 'installed' => 'Terpasang', 'completed' => 'Selesai', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'];
    public const TYPES = ServiceController::TYPES;

    public function index(Request $request)
    {
        $page = $this->remotePage('/v1/installations', $request, ['status', 'service_type']);

        return view('network.installations.index', compact('page') + ['statuses' => self::STATUSES, 'types' => self::TYPES]);
    }

    public function show(int $id)
    {
        $installation = $this->client()->get("/v1/installations/{$id}");

        return view('network.installations.show', compact('installation') + ['statuses' => self::STATUSES, 'types' => self::TYPES]);
    }

    public function create()
    {
        return view('network.installations.create', [
            'products' => $this->products(),
            'types' => self::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fullname' => 'required|string|min:3|max:100',
            'service_type' => 'required|in:' . implode(',', array_keys(self::TYPES)),
            'product_id' => 'required|integer',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'id_number' => 'nullable|string|max:30',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'nas_port_type' => 'nullable|in:Ethernet,Virtual,Async',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($data['service_type'] !== 'ppp') {
            unset($data['nas_port_type']);
        }

        $result = $this->client()->post('/v1/installations', array_filter($data, fn ($v) => $v !== null && $v !== ''));

        return redirect()->route('network.installations.show', $result['id'])
            ->with('success', 'Pengajuan pemasangan untuk ' . $data['fullname'] . ' tercatat. Aktifkan instalasi setelah teknisi selesai memasang.');
    }
}
