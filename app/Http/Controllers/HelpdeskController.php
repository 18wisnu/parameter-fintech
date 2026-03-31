<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Device;
use Illuminate\Support\Facades\Http;

class HelpdeskController extends Controller
{
    /**
     * Helper: baca nilai dari Virtual Parameter GenieACS.
     * GenieACS bisa mengembalikan nilai sebagai string biasa,
     * sebagai array {_value: ...}, atau null jika belum di-fetch.
     */
    private function getVP($data, string $vpKey, string $default = '-'): string
    {
        // Mencoba mengambil data dengan nested key (default data_get)
        $val = data_get($data, "VirtualParameters.{$vpKey}");

        // JIKA NULL, coba ambil dengan key literal (karena GenieACS Projection kadang return key yang ada titiknya)
        if ($val === null) {
            $literalKey = "VirtualParameters.{$vpKey}";
            $val = isset($data[$literalKey]) ? $data[$literalKey] : null;
        }

        if ($val === null) return $default;

        // Format 1: langsung string atau angka
        if (is_string($val) || is_numeric($val)) {
            $str = trim((string) $val);
            // Kalau VP script return "N/A", anggap tidak ada data
            return ($str !== '' && $str !== 'N/A') ? $str : $default;
        }

        // Format 2: array dengan key _value (paling umum dari GenieACS)
        if (is_array($val) && array_key_exists('_value', $val)) {
            $v = trim((string) $val['_value']);
            return ($v !== '' && $v !== 'N/A') ? $v : $default;
        }

        return $default;
    }

    /**
     * Coba beberapa nama VP secara berurutan, kembalikan nilai pertama yang ditemukan.
     * Berguna karena nama VP bisa berbeda tiap instalasi GenieACS.
     *
     * @param array  $keys    Daftar nama VP yang akan dicoba, urut dari prioritas tertinggi
     * @param string $default Nilai default jika semua key tidak ditemukan
     */
    private function getVPFirst($data, array $keys, string $default = '-'): string
    {
        foreach ($keys as $key) {
            $val = $this->getVP($data, $key);
            if ($val !== '-') return $val;
        }
        return $default;
    }

    /**
     * Ambil semua nama Virtual Parameter yang tersedia di sebuah device.
     * Berguna untuk diagnostik / debug.
     */
    private function getAvailableVPs($liveData): array
    {
        $vps = data_get($liveData, 'VirtualParameters', []);
        if (!is_array($vps)) return [];

        $result = [];
        foreach ($vps as $key => $val) {
            // Skip key internal GenieACS
            if (str_starts_with($key, '_')) continue;

            $value = '-';
            if (is_string($val) || is_numeric($val)) {
                $value = (string) $val;
            } elseif (is_array($val) && array_key_exists('_value', $val)) {
                $value = (string) $val['_value'];
            }
            $result[$key] = $value;
        }
        ksort($result);
        return $result;
    }

    /**
     * Dashboard: tampil semua device beserta relasi customer.
     */
    public function index()
    {
        $user = auth()->user();

        // JIKA YANG LOGIN ADALAH CLIENT
        if ($user->role === 'client') {
            // Cari perangkat yang terhubung ke customer ini
            $device = Device::whereHas('customer', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();

            if (!$device) {
                return view('client.no_device');
            }

            // Ambil data LIVE dari GenieACS (untuk SSID, Password, dan Daftar Host)
            $genieAcsUrl = config('services.genieacs.url');
            $liveInfo = ['uptime' => '-', 'ssid' => '-', 'password' => '-', 'hosts' => []];

            try {
                $projection = implode(',', [
                    'VirtualParameters.WlanSSID',
                    'VirtualParameters.WlanPassword',
                    'VirtualParameters.Uptime_Human',
                    'VirtualParameters.UptimeHuman',
                    'VirtualParameters.gettemp',
                    'VirtualParameters.Suhu',
                    'VirtualParameters.getponmode',
                    'VirtualParameters.PonMode',
                    'InternetGatewayDevice.LANDevice.1.Hosts.Host',
                    'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.AssociatedDevice',
                    'InternetGatewayDevice.WLANConfiguration.1.AssociatedDevice',
                ]);
                
                $response = Http::timeout(10)->get("{$genieAcsUrl}/devices/?query=" . urlencode(json_encode(['_id' => $device->genieacs_id])) . "&projection={$projection}");
                
                if ($response->status() === 200) {
                    $acsDataArray = json_decode($response->body(), true);
                    if (!empty($acsDataArray)) {
                        $acsData = $acsDataArray[0];
                        $liveInfo['ssid']     = $this->getVP($acsData, 'WlanSSID', $device->ssid);
                        $liveInfo['password'] = $this->getVP($acsData, 'WlanPassword', '********');
                        $liveInfo['uptime']   = $this->getVPFirst($acsData, ['Uptime_Human', 'UptimeHuman', 'uptime'], $device->uptime ?? '-');
                        $liveInfo['temp']     = $this->getVPFirst($acsData, ['gettemp', 'Suhu'], '-');
                        $liveInfo['pon']      = $this->getVPFirst($acsData, ['getponmode', 'PonMode'], 'GPON');
                        
                        // Parse Connected Devices (Agresif)
                        $possiblePaths = [
                            'InternetGatewayDevice.LANDevice.1.Hosts.Host',
                            'InternetGatewayDevice.Hosts.Host',
                            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.AssociatedDevice',
                            'InternetGatewayDevice.WLANConfiguration.1.AssociatedDevice',
                            'InternetGatewayDevice.WANDevice.1.WANDSLInterfaceConfig.AssociatedDevice',
                        ];
                        
                        foreach ($possiblePaths as $path) {
                            $found = data_get($acsData, $path, []);
                            if (!empty($found) && is_array($found)) {
                                foreach ($found as $key => $h) {
                                    if (!is_array($h) || str_starts_with((string)$key, '_')) continue;

                                    $mac = data_get($h, 'MACAddress._value', data_get($h, 'MACAddress', ''));
                                    // Untuk AssociatedDevice, MAC biasanya langsung di string atau di MACAddress._value
                                    if (!$mac && isset($h['_value'])) $mac = $h['_value']; 

                                    if ($mac) {
                                        $liveInfo['hosts'][] = [
                                            'name' => data_get($h, 'HostName._value', data_get($h, 'HostName', 'Device-' . $key)),
                                            'mac'  => $mac,
                                            'ip'   => data_get($h, 'IPAddress._value', data_get($h, 'IPAddress', '-')),
                                        ];
                                    }
                                }
                                if (!empty($liveInfo['hosts'])) break;
                            }
                        }

                        // Jika detail tetap kosong tapi jumlah di DB ada 
                        if (empty($liveInfo['hosts']) && $device->active_devices > 0) {
                            for ($i = 0; $i < $device->active_devices; $i++) {
                                $liveInfo['hosts'][] = [
                                    'name' => 'Device Terhubung (' . ($i + 1) . ')',
                                    'mac'  => 'Klik Perbarui di bawah untuk sinkronisasi',
                                    'ip'   => '-',
                                ];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }

            return view('client.dashboard', compact('device', 'liveInfo'));
        }

        // JIKA ADMIN / OWNER (Tampilan Tabel yang Mas punya sekarang)
        $devices = Device::with(['customer', 'site', 'odp'])->get();
        $sites = \App\Models\Site::all();
        $odps = \App\Models\Odp::all();

        // 📊 Dashboard Analytics: Hitung statistik kesehatan modem
        $stats = [
            'total'    => $devices->count(),
            'good'     => 0,
            'warning'  => 0,
            'critical' => 0,
            'offline'  => 0,
        ];

        foreach ($devices as $d) {
            $rx = $d->rx_power;
            $rxVal = (isset($rx) && $rx !== '-' && $rx !== '') ? floatval($rx) : null;

            if ($rxVal === null || $rxVal === 0) {
                $stats['offline']++;
            } elseif ($rxVal >= -22) {
                $stats['good']++;
            } elseif ($rxVal >= -26) {
                $stats['warning']++;
            } else {
                $stats['critical']++;
            }
        }

        return view('helpdesk.dashboard', compact('devices', 'sites', 'stats', 'odps'));
    }

    /**
     * Register pelanggan baru dan hubungkan ke device (Sekaligus buat Akun Client Portal).
     */
    public function registerCustomer(Request $request)
    {
        // 1. Validasi Input (Tambah phone & email, Lat Lng)
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'name'      => 'required|string|max:255',
            'address'   => 'nullable|string|max:500',
            'phone'     => 'required|string|max:30',
            'email'     => 'nullable|email|max:255',
            'site_id'   => 'nullable|exists:sites,id',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Gunakan Transaction agar jika error di tengah, tidak ada data setengah jadi
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $device = Device::findOrFail($request->device_id);

            // JIKA PELANGGAN LAMA (Sudah ada di DB, hanya Update Data)
            if ($device->customer_id) {
                $customer = Customer::findOrFail($device->customer_id);
                $customer->update([
                    'name'      => $request->name,
                    'address'   => $request->address,
                    'phone'     => $request->phone,
                    'email'     => $request->email,
                    'latitude'  => $request->latitude,
                    'longitude' => $request->longitude,
                ]);

                // Update juga nama & no hp di tabel User (akun loginnya) jika ada
                if ($customer->user) {
                    $customer->user->update([
                        'name'  => $request->name,
                        'phone' => $request->phone,
                        'email' => $request->email,
                    ]);
                }

                $msg = "Data pelanggan '{$customer->name}' berhasil diperbarui!";
            } 
            // JIKA PELANGGAN BARU
            else {
                // A. Cek apakah No HP sudah pernah terdaftar sebagai User sebelumnya
                $user = \App\Models\User::where('phone', $request->phone)->first();
                
                if (!$user) {
                    // BUAT AKUN LOGIN (ROLE CLIENT)
                    $user = \App\Models\User::create([
                        'name'     => $request->name,
                        'phone'    => $request->phone,
                        
                        // 1. Amankan Email (supaya tidak null)
                        'email'    => $request->email ?? $request->phone . '@parameter.net',
                        
                        // 2. Amankan Role
                        'role'     => 'client', 
                        
                        // 3. Amankan Password
                        'password' => \Illuminate\Support\Facades\Hash::make('12345678'),

                        // 4. Site ID: Samakan dengan site perangkat
                        'site_id'  => $request->site_id ?? $device->site_id,
                    ]);
                }

                // B. BUAT PROFIL PELANGGAN (CUSTOMER)
                $customer = Customer::create([
                    'user_id'   => $user->id, // Sambungkan ke Akun Login
                    'name'      => $request->name,
                    'phone'     => $request->phone,
                    'email'     => $request->email,
                    'address'   => $request->address,
                    'latitude'  => $request->latitude,
                    'longitude' => $request->longitude,
                    'status'    => 'active',
                ]);

                $msg = "Pelanggan '{$customer->name}' berhasil didaftarkan dan Akun Portal dibuat!";
            }

            $device->update([
                'customer_id' => $customer->id,
                'site_id'     => $request->site_id ?? $device->site_id,
                'odp_id'      => $request->odp_id ?? $device->odp_id,
            ]);

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', $msg);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal memproses data: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui Site perangkat
     */
    public function updateSite(Request $request)
    {
        $request->validate([
            'device_id' => 'required',
            'site_id'   => 'nullable|exists:sites,id',
            'odp_id'    => 'nullable|exists:odps,id',
        ]);

        $device = Device::findOrFail($request->device_id);
        $device->update([
            'site_id' => $request->site_id,
            'odp_id'  => $request->odp_id,
        ]);

        return back()->with('success', "Konfigurasi topologi '{$device->ssid}' berhasil diperbarui!");
    }

    /**
     * Ganti SSID & Password Wi-Fi via GenieACS task.
     */
    public function updateWifi(Request $request)
    {
        $request->validate([
            'genieacs_id' => 'required',
            'ssid'        => 'required',
            'password'    => 'required|min:8',
        ]);

        $user = auth()->user();

        // KEAMANAN: Jika user adalah CLIENT, pastikan dia hanya bisa ganti WiFi miliknya sendiri
        if ($user->role === 'client') {
            $ownDevice = Device::whereHas('customer', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('genieacs_id', $request->genieacs_id)->first();

            if (!$ownDevice) {
                return back()->with('error', 'Akses Ditolak! Anda tidak memiliki izin untuk mengubah perangkat ini.');
            }
        }

        $genieAcsUrl = config('services.genieacs.url');

        // Parameter yang akan diupdate secara massal (Dual Band Support)
        $parameterValues = [
            ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID", $request->ssid, "xsd:string"],
            ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.KeyPassphrase", $request->password, "xsd:string"],
            ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey", $request->password, "xsd:string"],
            
            // Support 5G (Indeks 2 atau 5 biasanya untuk 5G)
            ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.2.SSID", $request->ssid, "xsd:string"],
            ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.2.KeyPassphrase", $request->password, "xsd:string"],
            
            ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID", $request->ssid, "xsd:string"],
            ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.KeyPassphrase", $request->password, "xsd:string"],
            
            // Virtual Parameter (jika ada script di GenieACS)
            ["VirtualParameters.WlanPassword", $request->password, "xsd:string"],
            ["VirtualParameters.WlanSSID", $request->ssid, "xsd:string"],
        ];

        $payload = [
            "name"            => "setParameterValues",
            "parameterValues" => $parameterValues
        ];

        try {
            $encodedId = rawurlencode($request->genieacs_id);
            // Hapus ?connection_request agar tidak blocking timeout
            $response = Http::timeout(10)->post(
                "{$genieAcsUrl}/devices/{$encodedId}/tasks",
                $payload
            );

            if ($response->successful()) {
                return back()->with('success', 'Perintah Update Wi-Fi Antre! (Dukungan Dual-Band 2.4G & 5G). Tunggu 10-30 detik.');
            }
            return back()->with('error', 'Gagal mengirim perintah ke server GenieACS.');
        } catch (\Exception $e) {
            return back()->with('error', 'Koneksi ke GenieACS bermasalah: ' . $e->getMessage());
        }
    }

    /**
     * Ganti Username & Password PPPoE via GenieACS task.
     */
    public function updatePppoe(Request $request)
    {
        $request->validate([
            'genieacs_id'    => 'required',
            'pppoe_username' => 'required',
            'pppoe_password' => 'required',
        ]);

        $genieAcsUrl = config('services.genieacs.url');

        $payload = [
            "name"            => "setParameterValues",
            "parameterValues" => [
                ["InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username", $request->pppoe_username, "xsd:string"],
                ["InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password", $request->pppoe_password, "xsd:string"],
            ]
        ];

        try {
            $response = Http::timeout(15)->post(
                "{$genieAcsUrl}/devices/" . urlencode($request->genieacs_id) . "/tasks?connection_request",
                $payload
            );

            if ($response->successful()) {
                return back()->with('success', 'Berhasil! Instruksi perubahan Akun PPPoE dikirim. Modem akan diskonek sekian detik untuk dial ulang dengan akun baru.');
            }
            return back()->with('error', 'Gagal! Server GenieACS menolak pembaruan Kredensial PPPoE. Coba refresh status modem.');
        } catch (\Exception $e) {
            return back()->with('error', 'Server ACS tidak dapat dijangkau: ' . $e->getMessage());
        }
    }

    /**
     * Sync semua device dari GenieACS ke database lokal.
     */
    public function syncDevices()
    {
        $genieAcsUrl = config('services.genieacs.url');

        try {
            $projection = implode(',', [
                'VirtualParameters.RXPower',
                'VirtualParameters.Redaman',
                'VirtualParameters.WlanSSID',
                'VirtualParameters.IPPPPOE',
                'VirtualParameters.IPTR069',
                'VirtualParameters.SN',
                'VirtualParameters.Model',
                'VirtualParameters.activedevices',
                'InternetGatewayDevice.DeviceInfo.ModelName',
                'InternetGatewayDevice.DeviceInfo.SerialNumber',
                'Device.DeviceInfo.ModelName',
                'Device.DeviceInfo.SerialNumber',
                'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ExternalIPAddress',
            ]);

            $response = Http::timeout(30)->get("{$genieAcsUrl}/devices/?projection={$projection}");

            if ($response->successful()) {
                $count = 0;
                foreach ($response->json() as $device) {
                    $ssid      = $this->getVP($device, 'WlanSSID', data_get($device, 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID._value', '-'));
                    $ipPppoe   = $this->getVP($device, 'IPPPPOE',  data_get($device, 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ExternalIPAddress._value', '-'));
                    $ipTr069   = $this->getVP($device, 'IPTR069');
                    
                    // --- CARI SN (Lebih Agresif) ---
                    $sn = $this->getVPFirst($device, [
                        'getSerialNumber', 'SerialNumber', 'SN', 
                        'InternetGatewayDevice.DeviceInfo.SerialNumber',
                        'Device.DeviceInfo.SerialNumber'
                    ]);
                    
                    if ($sn === '-') {
                        $sn = data_get($device, 'InternetGatewayDevice.DeviceInfo.SerialNumber._value')
                           ?? data_get($device, 'Device.DeviceInfo.SerialNumber._value')
                           ?? $device['_id']; // Gunakan ID GenieACS jika SN benar-benar tidak ada
                    }

                    // --- CARI BRAND / MODEL ---
                    $brand = $this->getVP($device, 'Model');
                    if ($brand === '-') {
                        $brand = data_get($device, 'InternetGatewayDevice.DeviceInfo.ModelName._value')
                              ?? data_get($device, 'Device.DeviceInfo.ModelName._value')
                              ?? 'Unknown Modem';
                    }

                    $activeDev = $this->getVP($device, 'activedevices', '0');
                    $rxPower   = $this->getVPFirst($device, ['RXPower', 'Redaman', 'rxpower', 'Redaman_RX', 'rx_power']);

                    Device::updateOrCreate(
                        ['genieacs_id' => $device['_id']],
                        [
                            'brand'          => $brand,
                            'sn'             => $sn,
                            'ssid'           => $ssid,
                            'ip_pppoe'       => $ipPppoe,
                            'ip_tr069'       => $ipTr069,
                            'active_devices' => is_numeric($activeDev) ? $activeDev : '0',
                            'rx_power'       => $rxPower,
                        ]
                    );
                    $count++;
                }

                return back()->with('success', "Sync Sukses! {$count} modem berhasil sinkron. Semua perangkat dari GenieACS kini sudah masuk daftar.");
            }
            return back()->with('error', 'Gagal mendapatkan respons dari GenieACS. Cek koneksi server.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error Sistem: ' . $e->getMessage());
        }
    }

    /**
     * Hapus device yang teridentifikasi sebagai "Discovery" (tanpa data lengkap).
     */
    public function cleanupDiscovery()
    {
        $count = Device::where('brand', 'Unknown')
            ->orWhere('sn', '-')
            ->orWhere('sn', '')
            ->orWhereNull('sn')
            ->orWhereNull('brand')
            ->delete();

        return back()->with('success', "Pembersihan Selesai! {$count} perangkat discovery berhasil dihapus.");
    }

    /**
     * Detail device: ambil data live dari GenieACS dan parse VP dengan bersih.
     */
    public function show($id)
    {
        $device      = Device::with(['customer', 'site'])->findOrFail($id);
        $genieAcsUrl = config('services.genieacs.url');

        try {
            $query      = json_encode(["_id" => $device->genieacs_id]);
            $projection = 'VirtualParameters,' .
                          'InternetGatewayDevice.DeviceInfo.UpTime,' .
                          'Device.DeviceInfo.UpTime,' .
                          '_lastInform,_deviceId';

            $response = Http::timeout(15)->get(
                "{$genieAcsUrl}/devices/?query=" . urlencode($query) .
                "&projection=" . urlencode($projection)
            );

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data) && isset($data[0])) {
                    $liveData = $data[0];

                    $uptimeRaw = data_get($liveData, 'InternetGatewayDevice.DeviceInfo.UpTime._value')
                              ?? data_get($liveData, 'Device.DeviceInfo.UpTime._value');
                    $uptimeFallback = '-';
                    if ($uptimeRaw && is_numeric($uptimeRaw)) {
                        $secs = (int) $uptimeRaw;
                        $days = floor($secs / 86400);
                        $rem  = $secs % 86400;
                        $hrs  = str_pad(floor($rem / 3600), 2, '0', STR_PAD_LEFT);
                        $mins = str_pad(floor(($rem % 3600) / 60), 2, '0', STR_PAD_LEFT);
                        $sec  = str_pad($rem % 60, 2, '0', STR_PAD_LEFT);
                        $uptimeFallback = "{$days}d {$hrs}:{$mins}:{$sec}";
                    }

                    $info = [
                        'model'          => $this->getVP($liveData, 'Model', $device->brand ?? 'Unknown'),
                        'sn'             => $this->getVPFirst($liveData, ['getSerialNumber', 'SN'], $device->sn ?? '-'),
                        'ip_tr069'       => $this->getVPFirst($liveData, ['IPTR069', 'iptr069'], $device->ip_tr069 ?? '-'),
                        'ip_pppoe'       => $this->getVPFirst($liveData, ['pppoeIP', 'IPPPPOE'], $device->ip_pppoe ?? 'Offline'),
                        'ssid'           => $this->getVPFirst($liveData, ['WlanSSID', 'ssid'], $device->ssid ?? '-'),
                        'active_devices' => $this->getVPFirst($liveData, ['activedevices', 'ActiveDevices'], '0'),
                        'redaman'        => $this->getVPFirst($liveData, ['RXPower', 'Redaman']),
                        'suhu'           => $this->getVPFirst($liveData, ['gettemp', 'Suhu']),
                        'pppoe_user'     => $this->getVPFirst($liveData, ['pppoeUsername', 'pppoeUsername2']),
                        'pon_mac'        => $this->getVP($liveData, 'PonMac'),
                        'pon_mode'       => $this->getVPFirst($liveData, ['getponmode', 'PonMode']),
                        'uptime'         => (fn($vp) => $vp !== '-' ? $vp : $uptimeFallback)(
                                                 $this->getVPFirst($liveData, ['Uptime_Human', 'UptimeHuman'])
                                             ),
                        'last_inform'    => data_get($liveData, '_lastInform'),
                    ];

                    $vpDebug = $this->getAvailableVPs($liveData);

                    return view('device_detail', compact('device', 'liveData', 'info', 'vpDebug'));
                }
            }
            return back()->with('error', 'Data detail tidak ditemukan di server GenieACS untuk device ini.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyambung ke server GenieACS: ' . $e->getMessage());
        }
    }


    /**
     * Kirim perintah reboot ke modem via GenieACS.
     */
    public function reboot($id)
    {
        $device      = Device::findOrFail($id);
        $genieAcsUrl = config('services.genieacs.url');

        try {
            $encodedId = rawurlencode($device->genieacs_id);
            // Hapus ?connection_request agar tidak blocking/timeout
            $response  = Http::timeout(10)->post(
                "{$genieAcsUrl}/devices/{$encodedId}/tasks",
                ['name' => 'reboot']
            );

            if ($response->successful()) {
                return back()->with('success', 'Perintah Reboot Antre! Modem akan memulai ulang saat terhubung ke server (biasanya < 1 menit).');
            }
            return back()->with('error', 'Gagal mengirim perintah reboot ke modem.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Diagnostik Cepat: Refresh parameter utama dari modem.
     */
    public function diagnostic($id)
    {
        $device      = Device::findOrFail($id);
        $genieAcsUrl = config('services.genieacs.url');

        try {
            $encodedId = rawurlencode($device->genieacs_id);
            // Hapus ?connection_request agar tidak blocking/timeout
            $response  = Http::timeout(10)->post(
                "{$genieAcsUrl}/devices/{$encodedId}/tasks",
                ['name' => 'refreshObject', 'objectName' => 'InternetGatewayDevice']
            );

            if ($response->successful()) {
                return back()->with('success', 'Diagnostik Antre! Server sedang menginstruksikan modem untuk kirim data terbaru. Tunggu 10-20 detik lalu refresh.');
            }
            return back()->with('error', 'Gagal mengirim perintah diagnostik.');
        } catch (\Exception $e) {
            return back()->with('error', 'ACS tidak merespons: ' . $e->getMessage());
        }
    }
}