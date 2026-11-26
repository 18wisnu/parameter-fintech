<?php

namespace App\Http\Controllers\Network;

use App\Models\Customer;
use App\Services\Mixradius\SessionKinds;
use Illuminate\Http\Request;

/** Layanan internet (PPPoE/Hotspot/DHCP/Static) & pengguna online. Izin: services.read, radius.read, radius.disconnect. */
class ServiceController extends NetworkController
{
    public const STATUSES = ['active' => 'Aktif', 'pending' => 'Menunggu', 'suspended' => 'Isolir', 'expired' => 'Kedaluwarsa', 'inactive' => 'Nonaktif', 'terminated' => 'Berhenti'];
    public const TYPES = ['ppp' => 'PPPoE', 'hotspot' => 'Hotspot langganan', 'dhcp' => 'DHCP', 'static' => 'Static'];

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

    /** Pengguna online, dibedakan PPPoE / Hotspot langganan / Voucher hotspot. */
    public function online(Request $request)
    {
        $client = $this->client();
        $query = array_filter($request->only(['search', 'nas_ip']), fn ($v) => $v !== null && $v !== '');

        // Semua sesi diambil sekaligus (per 100) supaya bisa dikelompokkan per jenis
        $sessions = [];
        foreach ($client->paginate('/v1/radius/online', $query) as $s) {
            $sessions[] = $s;
            if (count($sessions) >= 2000) {
                break;
            }
        }

        $kinds = SessionKinds::map($this->business(), $client);
        $sessions = array_map(fn ($s) => $s + SessionKinds::of($kinds, $s['username'] ?? null), $sessions);

        $counts = collect($sessions)->countBy('kind');
        $kind = array_key_exists($request->query('kind'), SessionKinds::LABELS) ? $request->query('kind') : null;
        $filtered = $kind ? array_values(array_filter($sessions, fn ($s) => $s['kind'] === $kind)) : $sessions;

        $perPage = 50;
        $total = count($filtered);
        $pages = max(1, (int) ceil($total / $perPage));
        $current = min($pages, max(1, (int) $request->query('page', 1)));
        $page = ['items' => array_slice($filtered, ($current - 1) * $perPage, $perPage), 'page' => $current, 'total' => $total, 'total_pages' => $pages];

        return view('network.services.online', [
            'page' => $page,
            'kind' => $kind,
            'counts' => $counts,
            'all' => count($sessions),
            'labels' => SessionKinds::LABELS,
        ]);
    }

    public function disconnect(string $username)
    {
        $this->client()->disconnect($username);

        return back()->with('success', "Sesi {$username} diputus. Perangkat pelanggan akan login ulang otomatis.");
    }
}
