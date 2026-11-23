<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use App\Services\Mixradius\MixradiusClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MixradiusSyncTest extends TestCase
{
    use RefreshDatabase;

    private function connectedBusiness(string $email, string $name, string $url): array
    {
        $owner = User::factory()->create(['email' => $email]);
        $business = Business::registerFor($owner, $name);
        $business->update([
            'mixradius_enabled' => true,
            'mixradius_base_url' => $url,
            'mixradius_api_key' => 'mk_test',
            'mixradius_api_secret' => 'sk_test',
        ]);

        return [$owner, $business->fresh()];
    }

    private function fakePanel(string $host, array $customers, array $services): void
    {
        $page = fn (array $items) => Http::response(['success' => true, 'message' => 'OK', 'data' => [
            'items' => $items, 'total' => count($items), 'page' => 1, 'per_page' => 100, 'total_pages' => 1,
        ]]);

        Http::fake([
            "{$host}/api/v1/customers*" => $page($customers),
            "{$host}/api/v1/services*" => $page($services),
        ]);
    }

    public function test_base_url_is_normalized(): void
    {
        $this->assertSame('https://x.mixradius.com', MixradiusClient::normalizeBaseUrl('https://x.mixradius.com/api/v1/'));
        $this->assertSame('https://x.mixradius.com', MixradiusClient::normalizeBaseUrl('https://x.mixradius.com/api'));
        $this->assertSame('https://x.mixradius.com', MixradiusClient::normalizeBaseUrl(' https://x.mixradius.com '));
    }

    public function test_sync_pulls_customers_and_services_into_the_right_tenant(): void
    {
        [$ownerA, $bizA] = $this->connectedBusiness('a@test.com', 'Tenant A', 'https://a.mixradius.test');
        [$ownerB, $bizB] = $this->connectedBusiness('b@test.com', 'Tenant B', 'https://b.mixradius.test');

        // Data lokal lama tenant A dengan kode yang sama → harus dihubungkan, bukan diduplikasi
        $legacy = new Customer(['name' => 'Budi Lama', 'code' => 'CUST002', 'type' => 'pppoe']);
        $legacy->business_id = $bizA->id;
        $legacy->activated_by_id = $ownerA->id;
        $legacy->save();

        $this->fakePanel('a.mixradius.test', [
            ['id' => 1, 'customer_code' => 'CUST001', 'fullname' => 'John Doe', 'phone' => '628123', 'address' => 'Jl. Mawar 5', 'status' => 'active'],
            ['id' => 2, 'customer_code' => 'CUST002', 'fullname' => 'Budi', 'phone' => '628999', 'address' => null, 'status' => 'suspended'],
            ['id' => 3, 'customer_code' => 'CUST003', 'fullname' => 'Lama', 'status' => 'terminated'],
        ], [
            ['service_id' => 10, 'username' => 'ppp-john', 'customer_code' => 'CUST001', 'service_type' => 'ppp', 'status' => 'active',
                'expiry_date' => '2026-12-01', 'product' => ['name' => 'Home 20Mbps', 'price' => 200000]],
            ['service_id' => 11, 'username' => 'hs-budi', 'customer_code' => 'CUST002', 'service_type' => 'hotspot', 'status' => 'expired',
                'expiry_date' => '2026-11-01', 'product' => ['name' => 'Hotspot', 'price' => 50000]],
        ]);

        $this->actingAs($ownerA)->post(route('admin.mixradius.sync'))->assertSessionHas('success');

        $customers = Customer::withoutGlobalScope('tenant')->where('business_id', $bizA->id)->get()->keyBy('code');
        $this->assertCount(3, $customers);

        $john = $customers['CUST001'];
        $this->assertSame('John Doe', $john->name);
        $this->assertSame('pppoe', $john->type);
        $this->assertEquals(200000, $john->package_fee);
        $this->assertSame('active', $john->status);
        $this->assertSame('10', $john->mixradius_service_id);
        $this->assertSame('ppp-john', $john->mixradius_username);

        $budi = $customers['CUST002'];
        $this->assertSame($legacy->id, $budi->id);                 // dihubungkan
        $this->assertSame($ownerA->id, (int) $budi->activated_by_id); // data lokal tetap
        $this->assertSame('isolated', $budi->status);
        $this->assertTrue((bool) $budi->is_isolated);
        $this->assertSame('hotspot', $budi->type);

        $this->assertSame('inactive', $customers['CUST003']->status);
        $this->assertNotNull($bizA->fresh()->mixradius_last_sync_at);

        // Sinkron ulang tidak membuat duplikat
        $this->actingAs($ownerA)->post(route('admin.mixradius.sync'));
        $this->assertSame(3, Customer::withoutGlobalScope('tenant')->where('business_id', $bizA->id)->count());

        // Tenant B bisa punya kode CUST001 juga, dari panelnya sendiri
        $this->fakePanel('b.mixradius.test', [
            ['id' => 1, 'customer_code' => 'CUST001', 'fullname' => 'Orang Lain', 'status' => 'active'],
        ], []);
        $this->artisan('mixradius:sync', ['business' => $bizB->id])->assertSuccessful();

        $this->assertSame('Orang Lain', Customer::withoutGlobalScope('tenant')->where('business_id', $bizB->id)->value('name'));
        $this->assertSame('John Doe', Customer::withoutGlobalScope('tenant')->where('business_id', $bizA->id)->where('code', 'CUST001')->value('name'));

        Http::assertSent(fn ($r) => $r->hasHeader('X-API-Key', 'mk_test') && $r->hasHeader('X-API-Secret', 'sk_test'));
    }

    public function test_resellers_are_synced_and_selectable_for_voucher_deposit(): void
    {
        [$owner, $biz] = $this->connectedBusiness('a@test.com', 'Tenant A', 'https://a.mixradius.test');

        $page = fn (array $items) => Http::response(['success' => true, 'data' => [
            'items' => $items, 'total' => count($items), 'page' => 1, 'total_pages' => 1,
        ]]);
        Http::fake([
            'a.mixradius.test/api/v1/customers*' => $page([['id' => 1, 'customer_code' => 'C1', 'fullname' => 'Hotspot User', 'status' => 'active']]),
            'a.mixradius.test/api/v1/services*' => $page([['service_id' => 9, 'customer_code' => 'C1', 'service_type' => 'hotspot', 'status' => 'active', 'product' => ['price' => 5000]]]),
            'a.mixradius.test/api/v1/entities*' => $page([
                ['id' => 50, 'type' => 'reseller', 'name' => 'Toko Andi', 'code' => 'RS01', 'status' => 'active'],
                ['id' => 51, 'type' => 'outlet', 'name' => 'Warung Sari', 'status' => 'active'],
                ['id' => 1, 'type' => 'isp', 'name' => 'Pusat'],
            ]),
        ]);

        $this->actingAs($owner)->post(route('admin.mixradius.sync'))->assertSessionHas('success');
        $this->actingAs($owner)->post(route('admin.mixradius.sync')); // tidak duplikat

        $resellers = Customer::withoutGlobalScope('tenant')->where('business_id', $biz->id)->where('type', 'reseller')->get();
        $this->assertSame(['Toko Andi', 'Warung Sari'], $resellers->pluck('name')->sort()->values()->all());
        $this->assertFalse($resellers->first()->isLinked());
        $this->assertEquals(0, $resellers->first()->package_fee);

        $this->actingAs($owner)->get(route('mobile.deposits.create'))
            ->assertOk()
            ->assertSee('Toko Andi')
            ->assertSee('data-type="VOUCHER"', false);
    }

    public function test_permission_error_is_reported_clearly(): void
    {
        [$owner] = $this->connectedBusiness('a@test.com', 'Tenant A', 'https://a.mixradius.test');

        Http::fake(['a.mixradius.test/*' => Http::response(['success' => false, 'message' => 'Insufficient permission.'], 403)]);

        $this->actingAs($owner)->post(route('admin.mixradius.sync'))
            ->assertSessionHas('error', fn ($msg) => str_contains($msg, '403') && str_contains($msg, 'izin'));
    }
}
