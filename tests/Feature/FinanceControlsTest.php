<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\CashClosing;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\PeriodClosing;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Billing\InvoiceGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Tutup kas harian, umur piutang, laba rugi & tutup buku, jejak audit, prorata/diskon/biaya pasang. */
class FinanceControlsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-23 10:00:00'));
        $this->owner = User::factory()->create();
        $this->business = Business::registerFor($this->owner, 'RTRW Net');
        $this->owner->refresh();
        $this->actingAs($this->owner);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
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
        return Customer::create($attrs + ['name' => 'Budi', 'type' => 'pppoe', 'package_fee' => 150000, 'status' => 'active']);
    }

    private function invoice(Customer $c, string $period, array $attrs = []): Invoice
    {
        return Invoice::create($attrs + ['customer_id' => $c->id, 'invoice_number' => 'INV-' . $period . '-' . $c->id,
            'period_date' => $period, 'amount' => $c->package_fee, 'status' => 'unpaid']);
    }

    private function trx(string $type, string $code, float $amount, string $date): Transaction
    {
        return Transaction::create([
            'date' => $date, 'type' => $type, 'amount' => $amount, 'description' => "Uji {$code}",
            'account_id' => ChartOfAccount::byCode($code)->id,
            'source_account_id' => ChartOfAccount::byCode(ChartOfAccount::KAS)->id,
        ]);
    }

    // ── Tutup kas harian ───────────────────────────────────────────────

    public function test_cash_closing_compares_physical_cash_with_recorded_deposits(): void
    {
        $kolektor = $this->member('kolektor');
        $c = $this->customer();
        foreach ([100000, 150000] as $amount) {
            Deposit::create(['user_id' => $kolektor->id, 'customer_id' => $c->id, 'amount' => $amount, 'description' => 'Budi', 'status' => 'approved']);
        }

        $this->get(route('finance.cash.index'))->assertOk()->assertSee($kolektor->name)->assertSee('Rp 250.000');

        // Selisih tanpa catatan ditolak
        $this->post(route('finance.cash.store'), ['date' => '2026-11-23', 'user_id' => $kolektor->id, 'actual_amount' => 240000])
            ->assertSessionHas('error');
        $this->assertSame(0, CashClosing::count());

        $this->post(route('finance.cash.store'), ['date' => '2026-11-23', 'user_id' => $kolektor->id, 'actual_amount' => 240000, 'notes' => 'Uang kembalian'])
            ->assertSessionHas('warning', fn ($m) => str_contains($m, 'kurang'));

        $closing = CashClosing::first();
        $this->assertSame(2, $closing->deposit_count);
        $this->assertEquals(250000, $closing->expected_amount);
        $this->assertEquals(-10000, $closing->difference);

        // Tutup ulang memperbarui baris yang sama
        $this->post(route('finance.cash.store'), ['date' => '2026-11-23', 'user_id' => $kolektor->id, 'actual_amount' => 250000])
            ->assertSessionHas('success');
        $this->assertSame(1, CashClosing::count());
        $this->assertEquals(0, CashClosing::first()->difference);
    }

    // ── Umur piutang ───────────────────────────────────────────────────

    public function test_receivables_aging_buckets_and_collector_filter(): void
    {
        $kolektor = $this->member('kolektor');
        $a = $this->customer(['name' => 'Ani', 'collector_id' => $kolektor->id]);
        $b = $this->customer(['name' => 'Bayu']);

        $this->invoice($a, '2026-11-01');                 // 22 hari
        $this->invoice($a, '2026-09-01');                 // 83 hari
        $this->invoice($b, '2026-07-01');                 // 145 hari
        $this->invoice($b, '2026-10-01', ['status' => 'paid']);

        $data = \App\Services\Finance\Receivables::aging();
        $this->assertEquals(450000, $data['total']);
        $this->assertSame(1, $data['buckets']['0-30']['count']);
        $this->assertSame(1, $data['buckets']['61-90']['count']);
        $this->assertSame(1, $data['buckets']['90+']['count']);
        $this->assertSame('Bayu', $data['rows']->first()->customer->name); // tunggakan terlama di atas

        $this->get(route('finance.receivables.index', ['collector_id' => $kolektor->id]))
            ->assertOk()->assertSee('Ani')->assertDontSee('Bayu');
        $this->get(route('finance.receivables.index', ['collector_id' => 'none']))
            ->assertOk()->assertSee('Bayu')->assertDontSee('>Ani<', false);
        $this->get(route('finance.receivables.print'))->assertOk()->assertSee('Daftar Tagih')->assertSee('Rp 450.000');

        // Kolektor melihat daftar tagihnya di HP
        $this->actingAs($kolektor)->get(route('mobile.collections.index'))->assertOk()->assertSee('Ani')->assertDontSee('Bayu');
    }

    // ── Laba rugi & tutup buku ─────────────────────────────────────────

    public function test_profit_loss_and_period_closing_locks_transactions(): void
    {
        $this->trx('income', '4001', 200000, '2026-10-05');
        $this->trx('expense', '5001', 50000, '2026-10-06');
        $this->trx('income', '3001', 1000000, '2026-10-07'); // modal: di luar laba rugi
        $old = $this->trx('expense', '5004', 20000, '2026-10-08');

        $report = \App\Services\Finance\ProfitLoss::forMonth($this->business->id, Carbon::parse('2026-10-01'));
        $this->assertEquals(200000, $report['total_revenue']);
        $this->assertEquals(70000, $report['total_expense']);
        $this->assertEquals(130000, $report['net']);
        $this->assertEquals(1130000, $report['cash_balance']);

        $this->get(route('finance.pl.index', ['month' => '2026-10']))->assertOk()->assertSee('Rp 130.000')->assertSee('Tutup buku');

        // Teknisi tidak boleh menutup buku; bulan berjalan tidak bisa ditutup
        $this->actingAs($this->member('teknisi'))->post(route('finance.pl.close'), ['month' => '2026-10'])->assertRedirect(route('mobile.home'));
        $this->actingAs($this->owner)->post(route('finance.pl.close'), ['month' => '2026-11'])->assertSessionHas('error');

        $this->post(route('finance.pl.close'), ['month' => '2026-10'])->assertSessionHas('success');
        $closing = PeriodClosing::first();
        $this->assertEquals(130000, $closing->net);

        // Terkunci: tambah pengeluaran bertanggal Oktober ditolak
        $before = Transaction::count();
        $this->post(route('expenses.store'), [
            'date' => '2026-10-20', 'amount' => 5000, 'description' => 'Telat dicatat',
            'account_id' => ChartOfAccount::byCode('5004')->id, 'source_account_id' => ChartOfAccount::byCode('1001')->id,
        ])->assertSessionHas('error', fn ($m) => str_contains($m, 'sudah ditutup'));
        $this->assertSame($before, Transaction::count());
        $this->assertSame(0, \App\Models\Expense::count());

        // Hapus juga ditolak
        $this->delete(route('transactions.destroy', $old->id))->assertSessionHas('error');
        $this->assertNotNull(Transaction::find($old->id));

        // Bulan November tetap bisa
        $this->trx('expense', '5004', 1000, '2026-11-02');

        // Buka kembali → bisa dihapus
        $this->delete(route('finance.pl.reopen', $closing))->assertSessionHas('warning');
        $this->delete(route('transactions.destroy', $old->id))->assertSessionHas('success');
        $this->assertNull(Transaction::find($old->id));
    }

    // ── Jejak audit ────────────────────────────────────────────────────

    public function test_audit_trail_records_changes_per_tenant(): void
    {
        $c = $this->customer();
        $inv = $this->invoice($c, '2026-11-01');
        $inv->update(['amount' => 120000]);

        $log = ActivityLog::where('model_type', 'Invoice')->where('event', 'updated')->first();
        $this->assertNotNull($log);
        $this->assertSame($this->owner->id, $log->user_id);
        $this->assertSame($this->business->id, $log->business_id);
        $this->assertEquals(150000, $log->payload['old']['amount']);
        $this->assertEquals(120000, $log->payload['new']['amount']);
        $this->assertArrayNotHasKey('updated_at', $log->payload['new']);

        $this->get(route('admin.audit.index', ['model_type' => 'Invoice']))->assertOk()->assertSee('Ubah tagihan: ' . $inv->invoice_number, false);

        // Tenant lain tidak melihat log ini
        $other = User::factory()->create();
        Business::registerFor($other, 'Tenant Lain');
        $this->actingAs($other->refresh())->get(route('admin.audit.index'))->assertOk()->assertDontSee($inv->invoice_number);

        // Admin bisa membuka jejak audit (sama seperti pemilik)
        $this->actingAs($this->member('admin'))->get(route('admin.audit.index'))->assertOk()->assertSee($inv->invoice_number);
    }

    public function test_deleting_payment_requires_owner_and_reopens_invoice(): void
    {
        $c = $this->customer();
        $inv = $this->invoice($c, '2026-11-01');
        $this->post(route('invoices.paid', $inv->id))->assertSessionHas('success');
        $trx = Transaction::where('invoice_id', $inv->id)->first();
        $this->assertTrue((bool) $trx->is_locked);

        $this->actingAs($this->member('kolektor'))->delete(route('transactions.destroy', $trx->id))->assertRedirect(route('mobile.home'));
        $this->assertNotNull(Transaction::find($trx->id));

        $this->actingAs($this->owner)->delete(route('transactions.destroy', $trx->id))->assertSessionHas('warning');
        $this->assertNull(Transaction::find($trx->id));
        $this->assertSame('unpaid', $inv->fresh()->status);
        $this->assertTrue(ActivityLog::where('model_type', 'Transaction')->where('event', 'deleted')->exists());
    }

    // ── Prorata, diskon, biaya pasang ──────────────────────────────────

    public function test_invoice_lines_prorata_discount_and_installation_fee(): void
    {
        $c = $this->customer(['start_date' => '2026-11-16', 'discount' => 10000, 'installation_fee' => 250000]);
        $gen = new InvoiceGenerator($this->business);

        $nov = $gen->createFor($c, Carbon::parse('2026-11-01'));
        $this->assertEquals(75000 - 10000 + 250000, $nov->amount); // 15/30 hari dari 150.000
        $this->assertTrue($nov->details['prorata']);
        $this->assertCount(3, $nov->lines());
        $this->assertTrue($c->fresh()->installation_billed);

        $dec = $gen->createFor($c->fresh(), Carbon::parse('2026-12-01'));
        $this->assertEquals(140000, $dec->amount);
        $this->assertFalse($dec->details['prorata']);

        // Belum mulai berlangganan → tidak ditagih
        $later = $this->customer(['name' => 'Nanti', 'start_date' => '2027-01-10']);
        $this->assertNull($gen->createFor($later, Carbon::parse('2026-12-01')));

        // Pelanggan server jaringan: masa aktif diatur server, tanpa prorata
        $linked = $this->customer(['name' => 'Server', 'start_date' => '2026-11-16', 'mixradius_id' => '9']);
        $this->assertEquals(150000, $gen->createFor($linked, Carbon::parse('2026-11-01'))->amount);

        $this->get(route('invoices.print', $nov->id))->assertOk()->assertSee('Biaya pemasangan')->assertSee('prorata 15/30 hari');
        $this->get(route('invoices.print', ['id' => $nov->id, 'format' => 'pos']))->assertOk()->assertSee('Diskon');
    }

    public function test_customer_form_saves_billing_fields_and_can_bill_now(): void
    {
        $kolektor = $this->member('kolektor');

        $this->post(route('customers.store'), [
            'name' => 'Citra', 'type' => 'pppoe', 'package_fee' => 200000, 'status' => 'active',
            'collector_id' => $kolektor->id, 'discount' => 0, 'installation_fee' => 300000,
            'start_date' => '2026-11-01', 'bill_now' => 1,
        ])->assertSessionHas('success', fn ($m) => str_contains($m, 'Rp 500.000'));

        $c = Customer::where('name', 'Citra')->first();
        $this->assertSame($kolektor->id, $c->collector_id);
        $this->assertTrue($c->installation_billed);
        $this->assertSame(1, Invoice::where('customer_id', $c->id)->count());

        $this->get(route('customers.edit', $c))->assertOk()->assertSee('Sudah ditagihkan');
        $this->get(route('customers.show', $c))->assertOk()->assertSee($kolektor->name);
    }

    public function test_finance_pages_render_for_admin(): void
    {
        $admin = $this->member('admin');
        $this->actingAs($admin);
        foreach (['finance.cash.index', 'finance.receivables.index', 'finance.pl.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('finance.pl.index'))->assertOk()->assertDontSee('Hanya pemilik');
        $this->get(route('dashboard'))->assertOk()->assertSee('Tutup Kas Harian')->assertSee('Jejak Audit')->assertDontSee('API Integrasi');
    }

    public function test_deleting_deposit_transaction_cancels_the_deposit(): void
    {
        $kolektor = $this->member('kolektor');
        $c = $this->customer();
        $inv = $this->invoice($c, '2026-11-01');

        $this->actingAs($kolektor)->post(route('mobile.deposits.store'), ['customer_id' => $c->id, 'amount' => 150000]);
        $deposit = Deposit::firstOrFail();
        $this->assertSame('approved', $deposit->status);
        $this->assertSame('paid', $inv->fresh()->status);

        $trx = Transaction::where('deposit_id', $deposit->id)->firstOrFail();
        $this->actingAs($this->owner)->delete(route('transactions.destroy', $trx->id))
            ->assertSessionHas('warning', fn ($m) => str_contains($m, 'dibatalkan'));

        $this->assertSame('rejected', $deposit->fresh()->status);
        $this->assertSame('unpaid', $inv->fresh()->status);

        // Tutup kas tidak lagi menghitung setoran itu
        $this->post(route('finance.cash.store'), ['date' => '2026-11-23', 'user_id' => $kolektor->id, 'actual_amount' => 0]);
        $closing = CashClosing::firstOrFail();
        $this->assertEquals(0, $closing->expected_amount);
        $this->assertEquals(0, $closing->difference);

        // HP kolektor: tampil "Dibatalkan", total hari ini nol
        $this->actingAs($kolektor)->get(route('mobile.deposits.index'))->assertSee('Dibatalkan');
        $this->actingAs($kolektor)->get(route('mobile.home'))->assertSee('Dibatalkan')->assertSee('Rp 0');
    }
}
