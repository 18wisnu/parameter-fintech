<?php

namespace App\Http\Controllers\Network;

use App\Models\Customer;
use Illuminate\Http\Request;

/** Layanan internet (PPPoE/Hotspot/DHCP/Static) & pengguna online. Izin: services.read, radius.read, radius.disconnect. */
class ServiceController extends NetworkController
{
    public const STATUSES = ['active' => 'Aktif', 'pending' => 'Menunggu', 'suspended' => 'Isolir', 'expired' => 'Kedaluwarsa', 'inactive' => 'Nonaktif', 'terminated' => 'Berhenti'];
    public const TYPES = ['ppp' => 'PPPoE', 'hotspot' => 'Hotspot', 'dhcp' => 'DHCP', 'static' => 'Static'];

    public function index(Request $request)
    {
        $page = $this->remotePage('/v1/services', $request, ['status', 'service_type', 'search']);

        // Tautkan ke data pelanggan lokal bila ada
        $codes = collect($page['items'])->pluck('customer_code')->filter()->unique();
        $localCustomers = Customer::whereIn('code', $codes)->pluck('id', 'code');

        return view('network.services.index', compact('page', 'localCustomers') + [
            'statuses' => self::STATUSES,
            'types' => self::TYPES,
        ]);
    }

    public function online(Request $request)
    {
        $page = $this->remotePage('/v1/radius/online', $request, ['search', 'nas_ip'], 50);

        return view('network.services.online', compact('page'));
    }

    public function disconnect(string $username)
    {
        $this->client()->disconnect($username);

        return back()->with('success', "Sesi {$username} diputus. Perangkat pelanggan akan login ulang otomatis.");
    }
}
