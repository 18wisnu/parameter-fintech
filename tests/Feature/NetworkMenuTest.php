<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use App\Models\VoucherBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Menu Jaringan: data dari server jaringan tenant, tanpa menampilkan nama Mixradius. */
class NetworkMenuTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->business = Business::registerFor($this->owner, 'RTRW Net');
        $this->business->update([
            'mixradius_enabled' => true,
            'mixradius_base_url' => 'https://panel.test',
            'mixradius_api_key' => 'mk', 'mixradius_api_secret' => 'sk',
        ]);
        $this->owner->refresh();
        $this->fakePanel();
    }

    private function list(array $items): \GuzzleHttp\Promise\PromiseInterface
    {
        return Http::response(['success' => true, 'data' => ['items' => $items, 'total' => count($items), 'page' => 1, 'per_page' => 20, 'total_pages' => 1]]);
    }

    private function fakePanel(): void
    {
        Http::fake([
            'panel.test/api/v1/products/catalog*' => $this->list([
                ['id' => 12, 'name' => 'Hotspot 3 Jam', 'type' => 'hotspot', 'price' => 5000, 'bandwidth' => '5M/5M'],
                ['id' => 1, 'name' => 'Home 20Mbps', 'type' => 'ppp', 'price' => 200000],
            ]),
            'panel.test/api/v1/vouchers/restrictions*' => Http::response(['success' => true, 'data' => [
                ['id' => 0, 'name' => 'Global', 'type' => null, 'is_global' => 1],
                ['id' => 7, 'name' => 'Wilayah 1', 'type' => 'nas', 'is_global' => 0],
            ]]),
            'panel.test/api/v1/vouchers/generate' => Http::response(['success' => true, 'data' => [
                'batch_code' => 'BATCH-1', 'product' => 'Hotspot 3 Jam', 'quantity' => 2, 'entity_id' => 1,
                'deducted_amount' => null, 'new_balance' => null,
                'vouchers' => [['code' => 'VCAAA111', 'password' => 'p1'], ['code' => 'VCBBB222', 'password' => 'p2']],
            ]], 201),
            'panel.test/api/v1/vouchers/VCAAA111/status' => Http::response(['success' => true, 'data' => ['status' => 'inactive']]),
            'panel.test/api/v1/vouchers/VCAAA111' => Http::response(['success' => true, 'data' => [
                'code' => 'VCAAA111', 'password' => 'p1', 'status' => 'active', 'is_active' => true,
                'product' => ['name' => 'Hotspot 3 Jam', 'price' => 5000, 'limit_type' => 'time', 'time_limit' => 180, 'time_unit' => 'minute'],
                'usage' => ['total_sessions' => 1, 'total_time' => 5400, 'is_online' => true], 'sale' => null,
            ]]),
            'panel.test/api/v1/vouchers*' => $this->list([
                ['code' => 'VCAAA111', 'status' => 'active', 'product_name' => 'Hotspot 3 Jam', 'selling_price' => 5000, 'entity' => ['name' => 'Outlet A']],
            ]),
            'panel.test/api/v1/services*' => $this->list([
                ['service_id' => 10, 'username' => 'ppp-john', 'customer_code' => 'CUST001', 'customer_name' => 'John', 'service_type' => 'ppp',
                    'status' => 'active', 'is_online' => true, 'expiry_date' => '2026-12-01', 'product' => ['name' => 'Home 20Mbps', 'price' => 200000]],
            ]),
            'panel.test/api/v1/radius/disconnect/*' => Http::response(['success' => true, 'message' => 'Disconnect request sent.']),
            'panel.test/api/v1/radius/online*' => $this->list([
                ['username' => 'ppp-john', 'nas_ip' => '10.0.0.1', 'framed_ip' => '192.168.1.100', 'mac' => 'AA:BB', 'uptime' => 3600, 'download' => 52428800],
            ]),
            'panel.test/api/v1/tickets/5/reply' => Http::response(['success' => true, 'data' => ['reply_id' => 12]], 201),
            'panel.test/api/v1/tickets/5' => Http::response(['success' => true, 'data' => [
                'id' => 5, 'ticket_code' => 'TK-1', 'customer_name' => 'John', 'subject' => 'Internet lambat', 'description' => 'Putus-putus',
                'status' => 'open', 'priority' => 'high', 'category' => 'technical', 'replies' => [['message' => 'Segera dicek', 'reply_type' => 'staff', 'reply_by' => 'Admin']],
            ]]),
            'panel.test/api/v1/tickets' => function (Request $r) {
                return $r->method() === 'POST'
                    ? Http::response(['success' => true, 'data' => ['ticket_id' => 5, 'ticket_code' => 'TK-1']], 201)
                    : $this->list([['id' => 5, 'ticket_code' => 'TK-1', 'subject' => 'Internet lambat', 'customer_name' => 'John', 'status' => 'open', 'priority' => 'high', 'category' => 'technical']]);
            },
            'panel.test/api/v1/tickets*' => $this->list([['id' => 5, 'ticket_code' => 'TK-1', 'subject' => 'Internet lambat', 'status' => 'open']]),
            'panel.test/api/v1/installations/12' => Http::response(['success' => true, 'data' => ['id' => 12, 'fullname' => 'Jane', 'status' => 'new', 'service_type' => 'ppp', 'product_name' => 'Home 20Mbps']]),
            'panel.test/api/v1/installations' => function (Request $r) {
                return $r->method() === 'POST'
                    ? Http::response(['success' => true, 'data' => ['id' => 12, 'status' => 'new']], 201)
                    : $this->list([['id' => 12, 'fullname' => 'Jane', 'status' => 'new', 'service_type' => 'ppp']]);
            },
            'panel.test/api/v1/installations*' => $this->list([['id' => 12, 'fullname' => 'Jane', 'status' => 'new', 'service_type' => 'ppp']]),
            'panel.test/api/v1/entities/3' => Http::response(['success' => true, 'data' => ['id' => 3, 'name' => 'Reseller A', 'type' => 'reseller', 'balance' => 45000, 'stats' => ['voucher_stock' => 10, 'income_month' => 250000]]]),
            'panel.test/api/v1/entities*' => $this->list([['id' => 3, 'name' => 'Reseller A', 'type' => 'reseller', 'balance_type' => 'prepaid', 'balance' => 45000, 'status' => 'active']]),
            'panel.test/api/v1/reports/revenue*' => Http::response(['success' => true, 'data' => [
                'revenue_items' => ['invoice' => ['invoice_revenue' => 15000000, 'invoice_count' => 75], 'voucher' => ['voucher_revenue' => 3500000, 'voucher_count' => 120], 'total_revenue' => 19000000],
                'expense_items' => ['total_expense' => 5000000, 'commission' => 1000000], 'net_profit' => 14000000,
            ]]),
            'panel.test/api/v1/reports/customers*' => Http::response(['success' => true, 'data' => [
                'total_customers' => 350, 'by_status' => ['active' => 300, 'suspended' => 30], 'by_service_type' => ['ppp' => 250, 'hotspot' => 80], 'online_now' => 185, 'new_this_month' => 12,
            ]]),
        ]);
    }

    public function test_all_network_pages_render_without_showing_vendor_name(): void
    {
        $this->actingAs($this->owner);

        $pages = [
            route('network.vouchers.index') => 'VCAAA111',
            route('network.vouchers.create') => 'Hotspot 3 Jam',
            route('network.vouchers.show', 'VCAAA111') => 'ONLINE',
            route('network.vouchers.batches') => 'Arsip Batch',
            route('network.services.index') => 'ppp-john',
            route('network.online.index') => '192.168.1.100',
            route('network.tickets.index') => 'Internet lambat',
            route('network.tickets.show', 5) => 'Segera dicek',
            route('network.tickets.create') => 'Buat Tiket',
            route('network.installations.index') => 'Jane',
            route('network.installations.show', 12) => 'Home 20Mbps',
            route('network.installations.create') => 'Home 20Mbps',
            route('network.entities.index') => 'Reseller A',
            route('network.entities.show', 3) => 'Stok Voucher',
            route('network.reports.index') => 'Rp 14.000.000',
        ];

        foreach ($pages as $url => $expected) {
            $response = $this->get($url);
            $response->assertOk()->assertSee($expected, false);
            // Nama vendor tidak boleh muncul di halaman manapun
            $this->assertStringNotContainsStringIgnoringCase('mixradius', $response->getContent(), "Nama vendor bocor di {$url}");
        }

        // Sidebar menampilkan grup Jaringan
        $this->get(route('dashboard'))->assertSee('Voucher Hotspot')->assertSee('Pengguna Online');
    }

    public function test_generate_vouchers_archives_batch_and_prints(): void
    {
        $this->actingAs($this->owner);

        $response = $this->post(route('network.vouchers.store'), [
            'product_id' => 12, 'quantity' => 2, 'prefix' => 'vc', 'login_method' => 'user_pass',
            'code_combination' => 'upper_num', 'code_length' => 8, 'password_length' => 6, 'restriction_id' => 7,
        ]);

        $batch = VoucherBatch::first();
        $response->assertRedirect(route('network.vouchers.batches.show', $batch));
        $this->assertSame('BATCH-1', $batch->batch_code);
        $this->assertSame('Wilayah 1', $batch->restriction_name);
        $this->assertEquals(5000, $batch->price);
        $this->assertSame('VCBBB222', $batch->vouchers[1]['code']);

        // Password tersimpan terenkripsi
        $this->assertStringNotContainsString('VCAAA111', DB::table('voucher_batches')->value('vouchers'));

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/vouchers/generate')
            && $r['prefix'] === 'VC' && $r['restriction_id'] === 7 && $r['login_method'] === 'user_pass' && $r['quantity'] === 2);

        $this->get(route('network.vouchers.batches.print', $batch))->assertOk()
            ->assertSee('VCAAA111')->assertSee('Password: p1', false)->assertSee('RTRW Net');
    }

    public function test_global_area_is_not_sent_and_num_only_needs_length_8(): void
    {
        $this->actingAs($this->owner);

        $this->post(route('network.vouchers.store'), [
            'product_id' => 12, 'quantity' => 2, 'login_method' => 'voucher_code',
            'code_combination' => 'num_only', 'code_length' => 6, 'restriction_id' => 0,
        ])->assertSessionHasErrors('code_length');

        $this->post(route('network.vouchers.store'), [
            'product_id' => 12, 'quantity' => 2, 'login_method' => 'voucher_code',
            'code_combination' => 'upper_num', 'code_length' => 8, 'restriction_id' => 0,
        ]);

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/vouchers/generate')
            && !isset($r['restriction_id']) && !isset($r['password_length']));
    }

    public function test_actions_disable_voucher_disconnect_ticket_and_installation(): void
    {
        $this->actingAs($this->owner);

        $this->put(route('network.vouchers.status', 'VCAAA111'), ['status' => 'inactive', 'reason' => 'Hilang'])->assertSessionHas('success');
        Http::assertSent(fn (Request $r) => $r->method() === 'PUT' && $r['status'] === 'inactive' && $r['reason'] === 'Hilang');

        $this->post(route('network.online.disconnect', 'ppp-john'))->assertSessionHas('success');
        Http::assertSent(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/radius/disconnect/ppp-john'));

        // Tiket: id pelanggan lokal dipetakan ke id pelanggan di server
        $customer = Customer::create(['name' => 'John', 'code' => 'CUST001', 'type' => 'pppoe', 'mixradius_id' => '26']);
        $this->post(route('network.tickets.store'), [
            'customer_id' => $customer->id, 'subject' => 'Internet lambat', 'priority' => 'high', 'category' => 'technical',
        ])->assertRedirect(route('network.tickets.show', 5));
        Http::assertSent(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/api/v1/tickets') && $r['customer_id'] === 26);

        $this->post(route('network.tickets.reply', 5), ['message' => 'Teknisi meluncur'])->assertSessionHas('success');

        $this->post(route('network.installations.store'), [
            'fullname' => 'Jane Doe', 'service_type' => 'hotspot', 'product_id' => 12, 'nas_port_type' => 'Ethernet', 'phone' => '628999',
        ])->assertRedirect(route('network.installations.show', 12));
        // nas_port_type hanya untuk PPP
        Http::assertSent(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/api/v1/installations') && !isset($r['nas_port_type']));
    }

    public function test_access_rules(): void
    {
        // Kolektor diarahkan ke mobile
        $kolektor = User::factory()->create();
        $kolektor->businesses()->attach($this->business->id, ['role' => 'kolektor']);
        $kolektor->switchBusiness($this->business);
        $this->actingAs($kolektor)->get(route('network.vouchers.index'))->assertRedirect(route('mobile.home'));

        // Batch tenant lain tidak bisa dibuka
        $other = User::factory()->create();
        $otherBiz = Business::registerFor($other, 'Tenant Lain');
        $foreign = new VoucherBatch(['batch_code' => 'X', 'quantity' => 1, 'vouchers' => [['code' => 'RAHASIA']]]);
        $foreign->business_id = $otherBiz->id;
        $foreign->save();
        $this->actingAs($this->owner)->get(route('network.vouchers.batches.print', $foreign->id))->assertNotFound();

        // Tenant tanpa integrasi: menu tidak tampil & rute dialihkan
        $this->actingAs($other->fresh())->get(route('network.vouchers.index'))->assertRedirect(route('admin.mixradius.edit'));
        $this->actingAs($other->fresh())->get(route('dashboard'))->assertDontSee('Voucher Hotspot');
    }

    public function test_server_errors_are_shown_cleanly(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['panel.test/*' => Http::response(['success' => false, 'message' => 'Insufficient permission.'], 403)]);

        $this->actingAs($this->owner)->get(route('network.tickets.index'))
            ->assertStatus(502)->assertSee('Data jaringan tidak bisa dimuat')->assertSee('izin');

        $this->actingAs($this->owner)->from(route('network.vouchers.create'))
            ->post(route('network.vouchers.store'), ['product_id' => 12, 'quantity' => 1, 'login_method' => 'voucher_code', 'code_combination' => 'upper_num', 'code_length' => 8])
            ->assertRedirect(route('network.vouchers.create'))->assertSessionHas('error');
    }
}
