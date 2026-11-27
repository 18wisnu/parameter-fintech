<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menghapus data satu usaha langsung lewat query builder (tanpa scope tenant / event model),
 * selalu dibatasi WHERE business_id = usaha tersebut.
 */
class BusinessDataEraser
{
    /**
     * Kategori Reset Data. Urutan tabel = urutan hapus (anak dulu).
     * 'requires': kategori lain yang wajib ikut karena datanya saling terhubung.
     */
    public const CATEGORIES = [
        'finance' => [
            'label' => 'Keuangan',
            'description' => 'Buku kas (transaksi), tagihan, setoran, pemasukan & pengeluaran, gaji, tutup kas & tutup buku, dana cadangan, bagi hasil.',
            'tables' => ['deposits', 'transactions', 'invoices', 'incomes', 'expenses', 'salaries', 'cash_closings', 'period_closings', 'reserve_fund_logs', 'profit_distributions'],
            'requires' => [],
        ],
        'customers' => [
            'label' => 'Pelanggan & outlet',
            'description' => 'Semua pelanggan dan outlet voucher. Keuangan ikut terhapus karena tagihan & transaksi menempel ke pelanggan.',
            'tables' => ['customers'],
            'requires' => ['finance'],
        ],
        'vouchers' => [
            'label' => 'Riwayat cetak voucher',
            'description' => 'Daftar batch voucher yang pernah dibuat dari aplikasi. Voucher di server jaringan tidak terhapus.',
            'tables' => ['voucher_batches'],
            'requires' => [],
        ],
        'logs' => [
            'label' => 'Log aktivitas & jejak audit',
            'description' => 'Catatan login dan perubahan data di usaha ini.',
            'tables' => ['activity_logs'],
            'requires' => [],
        ],
    ];

    /** Tabel milik usaha yang tidak termasuk Reset Data, tapi ikut hilang saat usaha dihapus permanen. */
    private const BUSINESS_ONLY_TABLES = [
        'profit_sharing_settings', 'profit_sharing_stakeholders', 'business_invitations', 'subscription_payments',
    ];

    /** Lengkapi pilihan dengan kategori yang wajib ikut, urut sesuai CATEGORIES. */
    public static function resolve(array $categories): array
    {
        $chosen = array_values(array_intersect(array_keys(self::CATEGORIES), $categories));
        foreach ($chosen as $key) {
            $chosen = array_merge($chosen, self::CATEGORIES[$key]['requires']);
        }

        return array_values(array_intersect(array_keys(self::CATEGORIES), array_unique($chosen)));
    }

    /** Jumlah baris per kategori, untuk ditampilkan sebelum menghapus. */
    public static function counts(Business $business): array
    {
        $counts = [];
        foreach (self::CATEGORIES as $key => $cat) {
            $counts[$key] = array_sum(array_map(fn ($t) => self::query($business, $t)?->count() ?? 0, $cat['tables']));
        }

        return $counts;
    }

    /** @return array<string,int> baris terhapus per kategori */
    public static function reset(Business $business, array $categories): array
    {
        $categories = self::resolve($categories);
        $deleted = [];

        DB::transaction(function () use ($business, $categories, &$deleted) {
            foreach ($categories as $key) {
                $deleted[$key] = 0;
                foreach (self::CATEGORIES[$key]['tables'] as $table) {
                    $deleted[$key] += self::query($business, $table)?->delete() ?? 0;
                }
            }
        });

        Cache::forget("network:{$business->id}:outlet-stats");

        return $deleted;
    }

    /** Hapus permanen usaha beserta seluruh datanya. Akun pegawai tetap ada. */
    public static function purge(Business $business): void
    {
        DB::transaction(function () use ($business) {
            self::reset($business, array_keys(self::CATEGORIES));

            foreach (self::BUSINESS_ONLY_TABLES as $table) {
                self::query($business, $table)?->delete();
            }

            DB::table('users')->where('current_business_id', $business->id)->update(['current_business_id' => null]);
            DB::table('business_user')->where('business_id', $business->id)->delete();
            DB::table('businesses')->where('id', $business->id)->delete();
        });
    }

    private static function query(Business $business, string $table)
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'business_id')) {
            return null;
        }

        return DB::table($table)->where('business_id', $business->id);
    }
}
