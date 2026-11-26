<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Setoran & pengeluaran ke buku kas, tagihan bulanan vs server, cetak POS, akses per peran, analisa voucher. */
class FinanceBillingTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->business = Business::registerFor($this->owner, 'RTRW Net');
        $this->owner->refresh();
    }

    private function connect(): void
    {
        $this->business->update([
            'mixradius_enabled' => true, 'mixradius_base_url' => 'https://panel.test',
            'mixradius_api_key' => 'mk', 'mixradius_api_secret' => 'sk',
        ]);
    }

    private function member(string $role): User
    {
        $u = User::factory()->create();
        $this->business->users()->attach($u->id, ['role' => $role]);
        $u->switchBusiness($this->business);

        return $u->refresh();
    }

    private function customer(array $attrs = []): Customer
    {
        $c = new Customer($attrs + ['name' => 'Budi', 'type' => 'pppoe', 'package_fee' => 150000, 'status' => 'active']);
        $c->business_id = $this->business->id;
        $c->save();

        return $c;
    }

    private function invoice(Customer $c, array $attrs = []): Invoice
    {
        $i = new Invoice($attrs + ['customer_id' => $c->id, 'invoice_number' => 'INV-202611-' . $c->id, 'period_date' => '2026-11-01', 'amount' => $c->package_fee, 'status' => 'unpaid']);
        $i->business_id = $this->business->id;
        $i->save();

        return $i;
    }

    public function test_default_accounts_exist_after_migration(): void
    {
        foreach (['1001', '4001', '4002', '4003', '5001', '5004'] as $code) {
            $this->assertNotNull(ChartOfAccount::where('code', $code)->first(), "akun {$code}");
        }
    }

    public function test_collector_deposit_goes_straight_to_ledger_with_details(): void
    {
        $kolektor = $this->member('kolektor');
        $c = $this->customer(['code' => 'CUST001']);
        $old = $this->invoice($c, ['invoice_number' => 'INV-OKT', 'period_date' => '2026-10-01']);
        $this->invoice($c, ['invoice_number' => 'INV-NOV']);

        $this->actingAs($kolektor)->post(route('mobile.deposits.store'), [
            'customer_id' => $c->id, 'amount' => 150000, 'notes' => 'bayar di rumah',
        ])->assertRedirect(route('mobile.home'));

        $deposit = Deposit::firstOrFail();
        $this->assertSame('approved', $deposit->status);
        $this->assertSame($old->id, $deposit->invoice_id); // tagihan terlama dulu
        $this->assertSame('paid', $old->fresh()->status);

        $trx = Transaction::firstOrFail();
        $this->assertSame('income', $trx->type);
        $this->assertSame($kolektor->id, $trx->user_id);
        $this->assertSame($old->id, $trx->invoice_id);
        $this->assertSame($deposit->id, $trx->deposit_id);
        $this->assertSame('INV-OKT', $trx->reference);
        $this->assertSame('collector', $trx->payment_method);
        $this->assertSame(ChartOfAccount::where('code', '4001')->value('id'), $trx->account_id);
        $this->assertStringContainsString('Budi', $trx->description);

        $this->actingAs($this->owner)->get(route('reports.history'))->assertOk()
            ->assertSee('INV-OKT')->assertSee($kolektor->name)->assertSee('Setoran kolektor');
    }

    public function test_deposit_approval_feature_keeps_deposit_pending_until_confirmed(): void
    {
        $this->business->update(['enabled_features' => array_merge(Business::DEFAULT_FEATURES, ['deposit_approval' => true])]);
        $kolektor = $this->member('kolektor');
        $reseller = $this->customer(['name' => 'Konter Jaya', 'type' => 'reseller', 'package_fee' => 0]);

        $this->actingAs($kolektor)->post(route('mobile.deposits.store'), ['customer_id' => $reseller->id, 'amount' => 250000]);
        $this->assertSame('pending', Deposit::firstOrFail()->status);
        $this->assertSame(0, Transaction::count());

        $this->actingAs($this->owner)->get(route('dashboard'))->assertOk()->assertSee('Setoran menunggu konfirmasi');
        $this->post(route('admin.deposit.approveAll'))->assertSessionHas('success');

        $trx = Transaction::firstOrFail();
        $this->assertSame(ChartOfAccount::where('code', '4002')->value('id'), $trx->account_id); // voucher
        $this->assertSame($kolektor->id, $trx->user_id);
        $this->assertStringContainsString('Dikonfirmasi ' . $this->owner->name, $trx->notes);
    }

    public function test_expense_is_recorded_with_category_source_and_running_balance(): void
    {
        $kas = ChartOfAccount::where('code', '1001')->first();
        $bandwidth = ChartOfAccount::where('code', '5001')->first();
        $c = $this->customer();
        $this->actingAs($this->owner)->post(route('invoices.paid', $this->invoice($c)->id), ['payment_method' => 'transfer', 'notes' => 'BCA a.n. Budi']);

        $this->post(route('expenses.store'), [
            'date' => now()->toDateString(), 'amount' => 50000, 'description' => 'Bayar bandwidth',
            'notes' => 'Nota 123', 'account_id' => $bandwidth->id, 'source_account_id' => $kas->id,
        ])->assertRedirect(route('reports.history'));

        $exp = Transaction::where('type', 'expense')->firstOrFail();
        $this->assertSame($bandwidth->id, $exp->account_id);
        $this->assertSame($kas->id, $exp->source_account_id);
        $this->assertSame($this->owner->id, $exp->user_id);
        $this->assertNotNull($exp->expense_id);
        $this->assertStringContainsString('Nota 123', $exp->notes);

        $pay = Transaction::where('type', 'income')->firstOrFail();
        $this->assertSame('transfer', $pay->payment_method);
        $this->assertStringContainsString('BCA a.n. Budi', $pay->notes);

        $this->get(route('reports.history'))->assertOk()
            ->assertSee('Biaya Bandwidth')->assertSee('Rp 100.000') // saldo akhir 150.000 − 50.000
            ->assertSee('Transfer bank');
        $this->get(route('reports.history', ['type' => 'expense']))->assertOk()->assertSee('Bayar bandwidth')->assertDontSee('Pembayaran internet');
        $this->get(route('reports.history.excel'))->assertOk();
    }

    public function test_generate_skips_customers_already_paid_on_server_and_voids_old_invoices(): void
    {
        $this->connect();
        $paid = $this->customer(['name' => 'Sudah Bayar', 'code' => 'C1', 'mixradius_id' => '1']);
        $late = $this->customer(['name' => 'Nunggak', 'code' => 'C2', 'mixradius_id' => '2']);
        $local = $this->customer(['name' => 'Lokal']);
        $staleInvoice = $this->invoice($paid); // dibuat sebelum pelanggan bayar di server

        Http::fake([
            'panel.test/api/v1/services*' => Http::response(['success' => true, 'data' => ['items' => [
                ['service_id' => 11, 'username' => 'c1', 'customer_code' => 'C1', 'service_type' => 'ppp', 'status' => 'active', 'expiry_date' => '2026-12-05', 'product' => ['price' => 150000]],
                ['service_id' => 12, 'username' => 'c2', 'customer_code' => 'C2', 'service_type' => 'ppp', 'status' => 'suspended', 'expiry_date' => '2026-11-10', 'product' => ['price' => 150000]],
            ], 'total_pages' => 1]]),
            'panel.test/api/v1/customers*' => Http::response(['success' => true, 'data' => ['items' => [
                ['id' => 1, 'customer_code' => 'C1', 'fullname' => 'Sudah Bayar', 'status' => 'active'],
                ['id' => 2, 'customer_code' => 'C2', 'fullname' => 'Nunggak', 'status' => 'active'],
            ], 'total_pages' => 1]]),
        ]);

        $this->actingAs($this->owner)->post(route('invoices.generate'))->assertSessionHas('success');

        $this->assertSame('void', $staleInvoice->fresh()->status);
        $this->assertStringContainsString('Sudah dibayar di server', $staleInvoice->fresh()->void_reason);
        $this->assertSame(1, Invoice::where('customer_id', $late->id)->where('status', 'unpaid')->count());
        $this->assertSame(1, Invoice::where('customer_id', $local->id)->count());
        $this->assertSame(1, Invoice::where('customer_id', $paid->id)->count()); // tidak dibuat lagi
        $this->assertSame(0, Transaction::count()); // void tidak masuk buku kas

        $this->get(route('invoices.index', ['status' => 'void']))->assertOk()->assertSee('Lunas di server')->assertDontSee('Nunggak');
    }

    public function test_generate_aborts_when_server_unreachable(): void
    {
        $this->connect();
        $this->customer(['mixradius_id' => '1']);
        Http::fake(['panel.test/*' => Http::response('', 500)]);

        $this->actingAs($this->owner)->post(route('invoices.generate'))->assertSessionHas('error');
        $this->assertSame(0, Invoice::count());
    }

    public function test_monthly_command_runs_without_login(): void
    {
        $this->customer(['name' => 'Lokal A']);
        $this->artisan('billing:generate-invoices')->assertSuccessful();
        $this->assertSame(1, Invoice::withoutGlobalScopes()->where('business_id', $this->business->id)->count());
        $this->artisan('billing:generate-invoices')->assertSuccessful();
        $this->assertSame(1, Invoice::withoutGlobalScopes()->count()); // tidak dobel
    }

    public function test_pos_receipt_print(): void
    {
        $c = $this->customer(['code' => 'CUST9', 'product_name' => 'Home 10M']);
        $inv = $this->invoice($c);
        $this->actingAs($this->owner)->post(route('invoices.paid', $inv->id), ['payment_method' => 'qris']);

        $this->get(route('invoices.print', ['id' => $inv->id, 'format' => 'pos']))->assertOk()
            ->assertSee('58mm')->assertSee('LUNAS')->assertSee('QRIS')->assertSee('CUST9')->assertSee('RTRW Net');
        $this->get(route('invoices.print', ['id' => $inv->id, 'format' => 'pos', 'paper' => '80']))->assertOk()->assertSee('80mm');
        $this->get(route('invoices.printList', ['format' => 'pos']))->assertOk()->assertSee($inv->invoice_number);
    }

    public function test_network_menu_access_follows_role(): void
    {
        $this->connect();
        Http::fake(['panel.test/*' => Http::response(['success' => true, 'data' => ['items' => [], 'total_pages' => 1]])]);

        $admin = $this->member('admin');
        $teknisi = $this->member('teknisi');
        $kolektor = $this->member('kolektor');

        // Pengaturan API hanya pemilik
        $this->actingAs($admin)->get(route('admin.mixradius.edit'))->assertForbidden();
        $this->actingAs($admin)->get(route('network.vouchers.index'))->assertOk();
        // Admin = pemilik untuk menu lain, termasuk laporan jaringan & outlet
        $this->actingAs($admin)->get(route('network.reports.index'))->assertOk()->assertDontSee('API Integrasi');
        $this->actingAs($admin)->get(route('network.entities.index'))->assertOk();
        $this->actingAs($teknisi)->get(route('network.entities.index'))->assertRedirect(route('mobile.home'));

        $this->actingAs($teknisi)->get(route('network.tickets.index'))->assertOk()
            ->assertSee('Tiket Gangguan')->assertDontSee('Buku Kas');
        $this->actingAs($teknisi)->get(route('network.online.index'))->assertOk();
        $this->actingAs($teknisi)->get(route('network.vouchers.index'))->assertRedirect(route('mobile.home'));

        $this->actingAs($kolektor)->get(route('network.services.index'))->assertOk();
        $this->actingAs($kolektor)->get(route('network.tickets.index'))->assertRedirect(route('mobile.home'));
        $this->actingAs($kolektor)->get(route('mobile.home'))->assertOk()->assertSee('Cek Pelanggan')->assertDontSee('Tiket Gangguan');

        $this->actingAs($this->owner)->get(route('network.reports.index'))->assertOk();
    }

    public function test_voucher_analysis_ranks_resellers(): void
    {
        $this->connect();
        $stats = fn ($income, $trx) => ['income_month' => $income, 'trx_count_month' => $trx, 'voucher_gen_month' => 10, 'commission_month' => 1000, 'income_today' => 0, 'voucher_stock' => 5];
        Http::fake([
            'panel.test/api/v1/entities/3' => Http::response(['success' => true, 'data' => ['id' => 3, 'name' => 'Konter Kecil', 'type' => 'outlet', 'balance' => 0, 'stats' => $stats(50000, 9)]]),
            'panel.test/api/v1/entities/4' => Http::response(['success' => true, 'data' => ['id' => 4, 'name' => 'Warung Besar', 'type' => 'outlet', 'balance' => 0, 'stats' => $stats(900000, 2)]]),
            'panel.test/api/v1/entities/2' => Http::response(['success' => true, 'data' => ['id' => 2, 'name' => 'Induk Reseller', 'type' => 'reseller', 'balance' => 0, 'stats' => $stats(950000, 11)]]),
            'panel.test/api/v1/entities*' => Http::response(['success' => true, 'data' => ['items' => [
                ['id' => 1, 'name' => 'Pusat', 'type' => 'isp'],
                ['id' => 2, 'name' => 'Induk Reseller', 'type' => 'reseller'],
                ['id' => 3, 'name' => 'Konter Kecil', 'type' => 'outlet', 'parent_id' => 2],
                ['id' => 4, 'name' => 'Warung Besar', 'type' => 'outlet', 'parent_id' => 2],
            ], 'total_pages' => 1]]),
        ]);

        // Setoran Warung Besar di buku kas bulan ini → kolom "Disetor", bukan ditambahkan ke penjualan
        $this->actingAs($this->owner);
        $outlet = $this->customer(['name' => 'Warung Besar', 'type' => 'reseller', 'package_fee' => 0, 'mixradius_entity_id' => '4']);
        $trx = new Transaction([
            'date' => now()->toDateString(), 'type' => 'income', 'amount' => 600000, 'description' => 'Setoran voucher',
            'account_id' => ChartOfAccount::byCode(ChartOfAccount::PENDAPATAN_VOUCHER)->id, 'customer_id' => $outlet->id,
        ]);
        $trx->business_id = $this->business->id;
        $trx->user_id = $this->owner->id;
        $trx->save();

        $html = $this->actingAs($this->owner)->get(route('reports.vouchers'))->assertOk()
            ->assertSee('Rp 950.000')      // penjualan 2 outlet saja (reseller induk tidak ikut → bukan Rp 1.900.000)
            ->assertDontSee('Rp 1.900.000')->assertDontSee('Rp 1.550.000')
            ->assertSee('Rp 600.000')      // sudah disetor
            ->assertSee('Rp 350.000')      // belum disetor
            ->assertDontSee('Induk Reseller')
            ->assertSee('1 entitas reseller')
            ->assertDontSee('Pusat')->assertDontSee('ixradius')->getContent();
        $this->assertTrue(strpos($html, 'Warung Besar') < strpos($html, 'Konter Kecil'), 'urut pendapatan');

        \Illuminate\Support\Facades\Cache::flush();
        $html = $this->get(route('reports.vouchers', ['sort' => 'trx_count_month']))->assertOk()->getContent();
        $this->assertTrue(strpos($html, 'Konter Kecil') < strpos($html, 'Warung Besar'), 'urut transaksi');
    }

    public function test_voucher_analysis_without_network(): void
    {
        $this->actingAs($this->owner)->get(route('reports.vouchers'))->assertOk()->assertSee('Hubungkan server');
    }
}
