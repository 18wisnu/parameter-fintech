<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check()) {
            $method = $request->method();

            // 1. Log SEMUA Perubahan Data (POST, PUT, PATCH, DELETE)
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                $routeName = $request->route() ? $request->route()->getName() : 'unknown';
                $data = $request->all();

                // Mapping friendly activity name
                $activity = "Melakukan aksi ($method) di rute $routeName";
                
                // Custom Human-friendly Mapping
                switch ($routeName) {
                    case 'incomes.store':
                        $activity = "Mencatat Pemasukan: Rp " . number_format($data['amount'] ?? 0, 0, ',', '.') . " (" . ($data['description'] ?? '-') . ")";
                        break;
                    case 'expenses.store':
                        $activity = "Mencatat Pengeluaran: Rp " . number_format($data['amount'] ?? 0, 0, ',', '.') . " (" . ($data['description'] ?? '-') . ")";
                        break;
                    case 'reports.reserve.inject':
                        $activity = "Suntikan Dana (Tambah Modal) Kas sebesar Rp " . number_format($data['amount'] ?? 0, 0, ',', '.');
                        break;
                    case 'reports.reserve.store':
                        $activity = "Penggunaan Dana Cadangan sebesar Rp " . number_format($data['amount'] ?? 0, 0, ',', '.');
                        break;
                    case 'customers.store':
                        $activity = "Mendaftarkan Pelanggan Baru: " . ($data['name'] ?? 'N/A');
                        break;
                    case 'customers.update':
                        $activity = "Mengupdate Data Pelanggan: " . ($data['name'] ?? 'N/A');
                        break;
                    case 'customers.destroy':
                        $activity = "Menghapus Pelanggan dari Sistem";
                        break;
                    case 'mobile.deposits.store':
                        $activity = "Melaporkan Setoran Tunai Baru: Rp " . number_format($data['amount'] ?? 0, 0, ',', '.') . " (" . ($data['notes'] ?? '-') . ")";
                        break;
                    case 'admin.deposit.approve':
                        $activity = "Menyetujui (Verifikasi) Setoran Dana Staff";
                        break;
                    case 'invoices.paid':
                        $activity = "Melunasi Tagihan (Mark as Paid)";
                        break;
                    case 'admin.users.updateRole':
                        $activity = "Mengubah Jabatan/Role Pegawai menjadi " . strtoupper($data['role'] ?? '-');
                        break;
                    case 'admin.salaries.generate':
                        $activity = "Melakukan Generate Gaji Pegawai Bulanan";
                        break;
                    case 'admin.salaries.pay':
                        $activity = "Melakukan Pembayaran Gaji ke Pegawai";
                        break;
                    case 'admin.system.update':
                        $activity = "Menjalankan Update Aplikasi (Git Pull & Migrate)";
                        break;
                    case 'transactions.destroy':
                        $activity = "Menghapus Riwayat Transaksi Keuangan";
                        break;
                }

                $this->saveLog($request, $activity);
            }

            // 2. Log PENELUSURAN HALAMAN SENSITIF (GET)
            // Hanya log akses ke halaman Laporan, Log, dan Pengaturan Sistem
            if ($method === 'GET' && $request->route()) {
                $routeName = $request->route()->getName();
                $accessiblePage = null;

                if ($routeName === 'reports.index' || $routeName === 'reports.history') $accessiblePage = "Membuka Laporan Keuangan";
                if ($routeName === 'admin.logs.index') $accessiblePage = "Melihat Log Riwayat Sistem";
                if ($routeName === 'admin.system.index') $accessiblePage = "Membuka Panel Update Sistem";
                if ($routeName === 'admin.users.index') $accessiblePage = "Melihat Daftar Pegawai";

                if ($accessiblePage) {
                    $this->saveLog($request, $accessiblePage);
                }
            }
        }

        return $response;
    }

    private function saveLog($request, $activity)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => $activity,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $request->isMethod('GET') ? null : $request->except(['password', '_token', '_method', 'password_confirmation']),
        ]);
    }
}
