<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Mixradius\CustomerSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Profil pelanggan mengikuti server jaringan + cetak invoice lokal. */
class CustomerProfileTest extends TestCase
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
            'mixradius_enabled' => true,
            'mixradius_base_url' => 'https://panel.test',
            'mixradius_api_key' => 'mk', 'mixradius_api_secret' => 'sk',
        ]);
    }

    private function customer(array $attrs = []): Customer
    {
        $c = new Customer($attrs + ['name' => 'Budi', 'type' => 'pppoe', 'package_fee' => 150000, 'status' => 'active']);
        $c->business_id = $this->business->id;
        $c->save();

        return $c;
    }

    public function test_local_customer_crud_and_pages(): void
    {
        $this->actingAs($this->owner)->post(route('customers.store'), [
            'name' => 'Siti', 'customer_id' => 'C-01', 'type' => 'pppoe', 'package_fee' => 120000,
            'product_name' => 'Home 10M', 'phone' => '0812', 'email' => 'siti@example.test',
            'latitude' => '-6.2', 'longitude' => '106.8',
        ])->assertRedirect();

        $c = Customer::where('name', 'Siti')->firstOrFail();
        $this->assertSame('active', $c->status);
        $this->assertSame('siti@example.test', $c->email);

        $this->get(route('customers.index'))->assertOk()->assertSee('Siti')->assertSee('Home 10M');
        $this->get(route('customers.index', ['status' => 'isolated']))->assertOk()->assertDontSee('Siti');
        $this->get(route('customers.show', $c))->assertOk()->assertSee('Lihat lokasi di peta');
        $this->get(route('customers.edit', $c))->assertOk();

        $this->put(route('customers.update', $c), [
            'name' => 'Siti A', 'type' => 'pppoe', 'package_fee' => 120000, 'status' => 'active', 'is_isolated' => 1,
        ])->assertRedirect(route('customers.show', $c));
        $c->refresh();
        $this->assertSame('Siti A', $c->name);
        $this->assertSame('isolated', $c->displayStatus());
    }

    public function test_customer_id_unique_only_within_tenant(): void
    {
        $other = Business::registerFor(User::factory()->create(), 'Lain');
        $c = new Customer(['name' => 'X', 'type' => 'pppoe', 'customer_id' => 'C-01', 'package_fee' => 0]);
        $c->business_id = $other->id;
        $c->save();

        $this->actingAs($this->owner)->post(route('customers.store'), [
            'name' => 'Siti', 'customer_id' => 'C-01', 'type' => 'pppoe', 'package_fee' => 1,
        ])->assertSessionHasNoErrors();

        $this->get(route('customers.show', $c->id))->assertNotFound();
    }

    public function test_new_customer_is_created_on_network_server(): void
    {
        $this->connect();
        Http::fake([
            'panel.test/api/v1/products/catalog*' => Http::response(['success' => true, 'data' => ['items' => [
                ['id' => 1, 'name' => 'Home 20Mbps', 'type' => 'ppp', 'price' => 200000, 'bandwidth' => '20M/20M'],
            ], 'total_pages' => 1]]),
            'panel.test/api/v1/customers' => Http::response(['success' => true, 'data' => [
                'customer_id' => 15, 'customer_code' => 'CUST015', 'service_id' => 42, 'service_username' => 'CUST015',
            ]], 201),
        ]);

        $this->actingAs($this->owner)->get(route('customers.create'))->assertOk()->assertSee('Home 20Mbps')->assertDontSee('ixradius');

        $this->post(route('customers.store'), [
            'name' => 'Jane', 'product_id' => 1, 'billing_type' => 'prepaid', 'billing_cycle' => 'monthly', 'phone' => '628999',
        ])->assertRedirect();

        Http::assertSent(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/api/v1/customers')
            && $r['fullname'] === 'Jane' && $r['product_id'] === 1);

        $c = Customer::where('name', 'Jane')->firstOrFail();
        $this->assertSame('15', $c->mixradius_id);
        $this->assertSame('CUST015', $c->code);
        $this->assertSame('42', $c->mixradius_service_id);
        $this->assertSame('pppoe', $c->type);
        $this->assertEquals(200000, $c->package_fee);
        $this->assertSame('Home 20Mbps', $c->product_name);
    }

    public function test_linked_customer_profile_is_pushed_and_service_fields_locked(): void
    {
        $this->connect();
        $c = $this->customer(['mixradius_id' => '7', 'code' => 'CUST007', 'package_fee' => 250000]);

        Http::fake(['panel.test/api/v1/customers/7' => Http::response(['success' => true, 'data' => []])]);

        $this->actingAs($this->owner)->put(route('customers.update', $c), [
            'name' => 'Budi Baru', 'email' => 'b@example.test', 'package_fee' => 1, 'type' => 'hotspot',
        ])->assertRedirect(route('customers.show', $c));

        Http::assertSent(fn (Request $r) => $r->method() === 'PUT' && $r['fullname'] === 'Budi Baru' && $r['email'] === 'b@example.test');
        $c->refresh();
        $this->assertSame('Budi Baru', $c->name);
        $this->assertEquals(250000, $c->package_fee); // diatur server
        $this->assertSame('pppoe', $c->type);
    }

    public function test_rejected_push_keeps_local_data(): void
    {
        $this->connect();
        $c = $this->customer(['mixradius_id' => '7']);
        Http::fake(['panel.test/*' => Http::response(['success' => false, 'message' => 'Permission denied'], 403)]);

        $this->actingAs($this->owner)->from(route('customers.edit', $c))
            ->put(route('customers.update', $c), ['name' => 'Ganti'])
            ->assertRedirect(route('customers.edit', $c))
            ->assertSessionHas('error');

        $this->assertSame('Budi', $c->fresh()->name);
    }

    public function test_profile_shows_live_service_details(): void
    {
        $this->connect();
        $c = $this->customer(['mixradius_id' => '1', 'code' => 'CUST001']);
        Http::fake(['panel.test/api/v1/customers/1' => Http::response(['success' => true, 'data' => [
            'id' => 1, 'open_tickets_count' => 2,
            'services' => [['username' => 'john01', 'service_type' => 'ppp', 'status' => 'active', 'is_online' => true,
                'product_name' => 'Home 20Mbps', 'bandwidth_name' => '20M/20M', 'price' => 250000,
                'expiry_date' => '2026-12-02', 'area_name' => 'Area Utara', 'odp_name' => 'ODP-TDR-01']],
            'invoices_summary' => ['outstanding_total' => 999999],
        ]])]);

        $this->actingAs($this->owner)->get(route('customers.show', $c))->assertOk()
            ->assertSee('john01')->assertSee('Area Utara')->assertSee('ODP-TDR-01')->assertSee('2 tiket gangguan')
            ->assertDontSee('999.999')->assertDontSee('ixradius');
    }

    public function test_profile_still_renders_when_server_down(): void
    {
        $this->connect();
        $c = $this->customer(['mixradius_id' => '1', 'product_name' => 'Paket Lokal']);
        Http::fake(['panel.test/*' => Http::response('', 500)]);

        $this->actingAs($this->owner)->get(route('customers.show', $c))->assertOk()
            ->assertSee('Paket Lokal')->assertSee('belum bisa dimuat');
    }

    public function test_sync_fills_profile_fields(): void
    {
        $this->connect();
        Http::fake([
            'panel.test/api/v1/services*' => Http::response(['success' => true, 'data' => ['items' => [
                ['service_id' => 10, 'username' => 'john01', 'customer_code' => 'CUST001', 'service_type' => 'ppp', 'status' => 'active',
                    'billing_type' => 'prepaid', 'billing_cycle' => 'monthly', 'expiry_date' => '2026-12-02',
                    'product' => ['name' => 'Home 20Mbps', 'bandwidth' => '20M/20M', 'price' => 250000]],
            ], 'total_pages' => 1]]),
            'panel.test/api/v1/customers*' => Http::response(['success' => true, 'data' => ['items' => [
                ['id' => 1, 'customer_code' => 'CUST001', 'fullname' => 'John', 'email' => 'john@example.test', 'phone' => '628',
                    'address' => 'Jl. Mawar', 'latitude' => -6.2, 'longitude' => 106.81, 'status' => 'active',
                    'entity' => ['name' => 'Outlet A']],
            ], 'total_pages' => 1]]),
        ]);

        (new CustomerSync($this->business))->run();

        $c = Customer::withoutGlobalScope('tenant')->where('mixradius_id', '1')->firstOrFail();
        $this->assertSame('john@example.test', $c->email);
        $this->assertSame('Outlet A', $c->entity_name);
        $this->assertSame('Home 20Mbps', $c->product_name);
        $this->assertSame('20M/20M', $c->bandwidth);
        $this->assertSame('monthly', $c->billing_cycle);
        $this->assertEqualsWithDelta(-6.2, $c->latitude, 0.0001);
    }

    public function test_invoice_print_single_and_filtered(): void
    {
        $c = $this->customer(['code' => 'CUST009', 'product_name' => 'Home 10M']);
        $paid = new Invoice(['customer_id' => $c->id, 'invoice_number' => 'INV-1', 'period_date' => '2026-11-01', 'amount' => 150000, 'status' => 'paid']);
        $paid->business_id = $this->business->id;
        $paid->save();
        $unpaid = new Invoice(['customer_id' => $c->id, 'invoice_number' => 'INV-2', 'period_date' => '2026-12-01', 'amount' => 150000, 'status' => 'unpaid']);
        $unpaid->business_id = $this->business->id;
        $unpaid->save();

        $this->actingAs($this->owner)->get(route('invoices.print', $paid->id))->assertOk()
            ->assertSee('INV-1')->assertSee('LUNAS')->assertSee('CUST009')->assertSee('Home 10M')->assertSee('RTRW Net');

        $this->get(route('invoices.printList', ['status' => 'unpaid']))->assertOk()
            ->assertSee('INV-2')->assertDontSee('INV-1')->assertSee('BELUM BAYAR');

        $this->get(route('invoices.index'))->assertOk()->assertSee('Cetak');
    }

    public function test_invoice_print_is_tenant_scoped(): void
    {
        $other = Business::registerFor(User::factory()->create(), 'Lain');
        $oc = new Customer(['name' => 'Orang Lain', 'type' => 'pppoe', 'package_fee' => 1]);
        $oc->business_id = $other->id;
        $oc->save();
        $inv = new Invoice(['customer_id' => $oc->id, 'invoice_number' => 'INV-X', 'period_date' => '2026-11-01', 'amount' => 1, 'status' => 'unpaid']);
        $inv->business_id = $other->id;
        $inv->saveQuietly();

        $this->actingAs($this->owner)->get(route('invoices.print', $inv->id))->assertNotFound();
    }

    public function test_sidebar_groups_render(): void
    {
        $this->connect();
        Http::fake();
        $this->actingAs($this->owner)->get(route('customers.index'))->assertOk()
            ->assertSee('Keuangan')->assertSee('Jaringan')->assertSee('Pengaturan')->assertSee('Server Jaringan')
            ->assertDontSee('ixradius');
    }
}
