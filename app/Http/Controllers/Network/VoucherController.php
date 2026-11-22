<?php

namespace App\Http\Controllers\Network;

use App\Models\VoucherBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Voucher hotspot: daftar, generate batch, cetak, aktif/nonaktif.
 * Izin API: vouchers.read, vouchers.generate.
 */
class VoucherController extends NetworkController
{
    public const STATUSES = ['unused' => 'Belum dipakai', 'active' => 'Aktif', 'expired' => 'Kedaluwarsa', 'disabled' => 'Dinonaktifkan'];

    public function index(Request $request)
    {
        $page = $this->remotePage('/v1/vouchers', $request, ['status', 'search', 'batch_code', 'product_id']);
        $products = $this->products('hotspot');

        return view('network.vouchers.index', compact('page', 'products') + ['statuses' => self::STATUSES]);
    }

    public function show(string $code)
    {
        $voucher = $this->client()->get('/v1/vouchers/' . rawurlencode($code));

        return view('network.vouchers.show', compact('voucher') + ['statuses' => self::STATUSES]);
    }

    public function create()
    {
        return view('network.vouchers.create', [
            'products' => $this->products('hotspot'),
            'restrictions' => $this->restrictions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1|max:500',
            'prefix' => 'nullable|string|max:10|alpha_num',
            'login_method' => 'required|in:voucher_code,user_pass',
            'code_combination' => 'required|in:upper_num,lower_num,upper_lower,upper_lower_num,upper_only,lower_only,num_only',
            'code_length' => 'required|integer|min:5|max:16',
            'password_length' => 'nullable|integer|min:5|max:16',
            'mac_binding' => 'nullable|boolean',
            'restriction_id' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:200',
        ]);

        if ($data['code_combination'] === 'num_only' && $data['code_length'] < 8) {
            return back()->withInput()->withErrors(['code_length' => 'Kombinasi angka saja membutuhkan panjang kode minimal 8.']);
        }

        $payload = array_filter([
            'product_id' => (int) $data['product_id'],
            'quantity' => (int) $data['quantity'],
            'prefix' => !empty($data['prefix']) ? strtoupper($data['prefix']) : null,
            'login_method' => $data['login_method'],
            'code_combination' => $data['code_combination'],
            'code_length' => (int) $data['code_length'],
            'password_length' => $data['login_method'] === 'user_pass' ? (int) ($data['password_length'] ?? 6) : null,
            'mac_binding' => $request->boolean('mac_binding') ? 1 : 0,
            // 0 = Global (tanpa area) → tidak dikirim
            'restriction_id' => !empty($data['restriction_id']) ? (int) $data['restriction_id'] : null,
            'notes' => $data['notes'] ?? null,
        ], fn ($v) => $v !== null);

        $result = $this->client()->post('/v1/vouchers/generate', $payload);

        $product = collect($this->products('hotspot'))->firstWhere('id', (int) $data['product_id']);
        $restriction = collect($this->restrictions())->firstWhere('id', (int) ($data['restriction_id'] ?? 0));

        $batch = VoucherBatch::create([
            'batch_code' => $result['batch_code'] ?? ('BATCH-' . now()->format('YmdHis')),
            'product_id' => (int) $data['product_id'],
            'product_name' => $result['product'] ?? ($product['name'] ?? null),
            'price' => (float) ($product['price'] ?? 0),
            'quantity' => (int) ($result['quantity'] ?? count($result['vouchers'] ?? [])),
            'prefix' => $payload['prefix'] ?? null,
            'login_method' => $data['login_method'],
            'restriction_name' => $restriction['name'] ?? 'Global',
            'vouchers' => array_values(array_map(fn ($v) => [
                'code' => $v['code'] ?? '',
                'password' => $v['password'] ?? null,
            ], $result['vouchers'] ?? [])),
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $msg = "{$batch->quantity} voucher berhasil dibuat (batch {$batch->batch_code}).";
        if (isset($result['deducted_amount']) && $result['deducted_amount'] !== null) {
            $msg .= ' Saldo terpotong Rp ' . number_format($result['deducted_amount'], 0, ',', '.') . '.';
        }

        return redirect()->route('network.vouchers.batches.show', $batch)->with('success', $msg);
    }

    public function updateStatus(Request $request, string $code)
    {
        $data = $request->validate([
            'status' => 'required|in:active,inactive',
            'reason' => 'nullable|string|max:200',
        ]);

        $this->client()->put('/v1/vouchers/' . rawurlencode($code) . '/status', array_filter($data));

        return back()->with('success', "Voucher {$code} " . ($data['status'] === 'active' ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    // ---- Arsip batch (lokal) ----

    public function batches()
    {
        $batches = VoucherBatch::with('creator')->latest()->paginate(20);

        return view('network.vouchers.batches', compact('batches'));
    }

    public function showBatch(VoucherBatch $batch)
    {
        return view('network.vouchers.batch', compact('batch'));
    }

    public function printBatch(VoucherBatch $batch)
    {
        return view('network.vouchers.print', [
            'batch' => $batch,
            'businessName' => $this->business()->name,
        ]);
    }

    /** Pilihan area login. id 0 = Global. */
    private function restrictions(): array
    {
        $business = $this->business();

        return Cache::remember("network:{$business->id}:restrictions", 600, function () {
            $list = $this->client()->get('/v1/vouchers/restrictions');

            return array_is_list($list) ? $list : ($list['items'] ?? []);
        });
    }
}
