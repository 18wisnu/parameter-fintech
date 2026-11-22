<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Opsi A: aplikasi ini billing utama. Tagihan lunas → layanan diperpanjang di Mixradius.
 */
class ServiceRenewalTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Customer $customer;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([['1001', 'Kas', 'asset'], ['4001', 'Pendapatan PPPoE', 'revenue']] as [$code, $name, $type]) {
            DB::table('chart_of_accounts')->insert(['code' => $code, 'name' => $name, 'type' => $type, 'created_at' => now(), 'updated_at' => now()]);
        }

        $this->owner = User::factory()->create();
        $this->business = Business::registerFor($this->owner, 'RTRW Net');
        $this->business->update([
            'mixradius_enabled' => true,
            'mixradius_base_url' => 'https://panel.test',
            'mixradius_api_key' => 'mk', 'mixradius_api_secret' => 'sk',
        ]);
        $this->owner->refresh();

        $this->actingAs($this->owner);

        $this->customer = Customer::create([
            'name' => 'John', 'code' => 'CUST001', 'type' => 'pppoe', 'package_fee' => 200000,
            'is_isolated' => true, 'status' => 'isolated', 'due_date' => '2026-11-01',
            'mixradius_id' => '1', 'mixradius_service_id' => '10', 'mixradius_username' => 'ppp-john',
        ]);

        $this->invoice = Invoice::create([
            'customer_id' => $this->customer->id, 'invoice_number' => 'INV-202611-0001',
            'period_date' => '2026-11-01', 'amount' => 200000, 'status' => 'unpaid',
        ]);
    }

    private function fakePanel(string $expiry, $renew): void
    {
        // Mulai dari factory bersih: Http::fake() kedua tidak menimpa stub yang lama
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake([
            'panel.test/api/v1/services/10/renew' => $renew,
            'panel.test/api/v1/services*' => Http::response(['success' => true, 'data' => [
                'items' => [['service_id' => 10, 'username' => 'ppp-john', 'expiry_date' => $expiry]],
                'total' => 1, 'page' => 1, 'per_page' => 100, 'total_pages' => 1,
            ]]),
        ]);
    }

    private function renewOk(): \GuzzleHttp\Promise\PromiseInterface
    {
        return Http::response(['success' => true, 'message' => 'Service renewed.', 'data' => [
            'service_id' => 10, 'renewed' => true, 'invoice_number' => 'INV-2026-000245', 'new_expiry' => '2026-12-01',
        ]], 201);
    }

    public function test_marking_invoice_paid_renews_service_and_opens_isolation(): void
    {
        $this->fakePanel('2026-11-01', $this->renewOk());

        $this->post(route('invoices.paid', $this->invoice->id))
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'Layanan internet aktif s/d 01 Dec 2026'));

        $inv = $this->invoice->fresh();
        $this->assertSame('paid', $inv->status);
        $this->assertSame('success', $inv->renewal_status);
        $this->assertSame('INV-2026-000245', $inv->renewal_remote_invoice);

        $c = $this->customer->fresh();
        $this->assertFalse((bool) $c->is_isolated);
        $this->assertSame('active', $c->status);
        $this->assertSame('2026-12-01', \Carbon\Carbon::parse($c->due_date)->toDateString());

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/api/v1/services/10/renew')
            && $r['reference'] === 'INV-202611-0001' && $r['payment_method'] === 'cash');
    }

    public function test_approving_collector_deposit_also_renews(): void
    {
        $this->fakePanel('2026-11-01', $this->renewOk());

        $kolektor = User::factory()->create();
        $kolektor->businesses()->attach($this->business->id, ['role' => 'kolektor']);

        $deposit = Deposit::create([
            'user_id' => $kolektor->id, 'customer_id' => $this->customer->id, 'invoice_id' => $this->invoice->id,
            'amount' => 200000, 'description' => 'John', 'status' => 'pending',
        ]);

        $this->post(route('admin.deposit.approve', $deposit->id))->assertSessionHas('success');

        $this->assertSame('success', $this->invoice->fresh()->renewal_status);
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/renew') && str_contains($r['notes'], 'Setoran kolektor'));
    }

    public function test_failure_is_recorded_without_losing_payment_and_retry_does_not_double_renew(): void
    {
        // Renew time-out: request sampai ke Mixradius tapi jawabannya tidak diterima
        $this->fakePanel('2026-11-01', fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));

        $this->post(route('invoices.paid', $this->invoice->id))->assertSessionHas('warning');

        $inv = $this->invoice->fresh();
        $this->assertSame('paid', $inv->status);              // pembayaran tetap tercatat
        $this->assertSame('failed', $inv->renewal_status);
        $this->assertSame(1, DB::table('transactions')->count());

        // Ternyata Mixradius sudah memperpanjang → retry TIDAK boleh memanggil renew lagi
        $this->fakePanel('2026-12-01', $this->renewOk());
        $this->artisan('mixradius:retry-renewals')->assertSuccessful();

        $this->assertSame('success', $this->invoice->fresh()->renewal_status);
        Http::assertNotSent(fn (Request $r) => str_ends_with($r->url(), '/renew'));
    }

    public function test_rejected_renewal_can_be_retried_manually(): void
    {
        $this->fakePanel('2026-11-01', Http::response(['success' => false, 'message' => 'Renewal window closed.'], 422));

        $this->post(route('invoices.paid', $this->invoice->id));
        $this->assertSame('failed', $this->invoice->fresh()->renewal_status);
        $this->assertStringContainsString('Renewal window closed', $this->invoice->fresh()->renewal_error);

        $this->fakePanel('2026-11-01', $this->renewOk());
        $this->post(route('invoices.renewal.retry', $this->invoice->id))->assertSessionHas('success');

        $this->assertSame('success', $this->invoice->fresh()->renewal_status);
        $this->get(route('invoices.index'))->assertOk()->assertSee('Internet aktif');
    }

    public function test_customer_not_linked_to_server_is_not_renewed(): void
    {
        Http::fake();
        $this->customer->update(['mixradius_service_id' => null]);

        $this->post(route('invoices.paid', $this->invoice->id))->assertSessionHas('success');

        $this->assertNull($this->invoice->fresh()->renewal_status);
        Http::assertNothingSent();
    }

    public function test_isolated_customers_still_get_invoices(): void
    {
        $this->invoice->delete();

        $this->post(route('invoices.generate'))->assertSessionHas('success');

        $this->assertSame(1, Invoice::where('customer_id', $this->customer->id)->count());
    }
}
