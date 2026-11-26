<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** PPPoE vs hotspot langganan vs voucher hotspot selalu dibedakan. */
class HotspotKindsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->business = Business::registerFor($this->owner, 'RTRW Net');
        $this->business->update(['mixradius_enabled' => true, 'mixradius_base_url' => 'https://panel.test', 'mixradius_api_key' => 'k', 'mixradius_api_secret' => 's']);
        $this->owner->refresh();
        $this->actingAs($this->owner);
    }

    private function list(array $items)
    {
        return Http::response(['success' => true, 'data' => ['items' => $items, 'total' => count($items), 'page' => 1, 'total_pages' => 1]]);
    }

    public function test_online_sessions_are_split_into_pppoe_hotspot_subscription_and_voucher(): void
    {
        $budi = Customer::create(['name' => 'Budi PPP', 'code' => 'C1', 'type' => 'pppoe', 'package_fee' => 150000, 'status' => 'active']);
        Http::fake([
            'panel.test/api/v1/radius/online*' => $this->list([
                ['username' => 'ppp-budi', 'framed_ip' => '10.1.0.2', 'uptime' => 7200, 'download' => 1048576],
                ['username' => 'hs-sari', 'framed_ip' => '10.5.0.9', 'uptime' => 60],
                ['username' => 'VC8KX2', 'framed_ip' => '10.5.0.10', 'uptime' => 300],
                ['username' => 'VC9ZZ1', 'framed_ip' => '10.5.0.11', 'uptime' => 300],
            ]),
            'panel.test/api/v1/services*' => $this->list([
                ['username' => 'ppp-budi', 'service_type' => 'ppp', 'customer_code' => 'C1'],
                ['username' => 'hs-sari', 'service_type' => 'hotspot', 'customer_code' => 'C2'],
            ]),
        ]);

        $this->get(route('network.online.index'))->assertOk()
            ->assertSeeInOrder(['Semua', '4', 'PPPoE', '1', 'Hotspot langganan', '1', 'Voucher hotspot', '2'])
            ->assertSee('Budi PPP')->assertSee('VC8KX2');

        $this->get(route('network.online.index', ['kind' => 'voucher']))->assertOk()
            ->assertSee('VC8KX2')->assertSee('VC9ZZ1')->assertDontSee('ppp-budi')->assertDontSee('hs-sari');
        $this->get(route('network.online.index', ['kind' => 'pppoe']))->assertOk()
            ->assertSee('ppp-budi')->assertDontSee('VC8KX2');
    }

    public function test_hotspot_subscriber_deposit_is_subscription_not_voucher_income(): void
    {
        Http::fake();
        $this->business->update(['mixradius_enabled' => false]);
        $kolektor = User::factory()->create();
        $this->business->users()->attach($kolektor->id, ['role' => 'kolektor']);
        $kolektor->switchBusiness($this->business);

        $hs = Customer::create(['name' => 'Sari Hotspot', 'type' => 'hotspot', 'package_fee' => 50000, 'status' => 'active']);
        $rs = Customer::create(['name' => 'Konter Jaya', 'type' => 'reseller', 'package_fee' => 0, 'status' => 'active']);

        $this->actingAs($kolektor->refresh())->get(route('mobile.deposits.create'))->assertOk()
            ->assertSee('Voucher (Reseller / Outlet)')
            ->assertSee('value="' . $hs->id . '" data-type="MONTHLY"', false)
            ->assertSee('value="' . $rs->id . '" data-type="VOUCHER"', false);

        $this->post(route('mobile.deposits.store'), ['customer_id' => $hs->id, 'amount' => 50000]);
        $this->post(route('mobile.deposits.store'), ['customer_id' => $rs->id, 'amount' => 200000]);

        $code = fn (Customer $c) => Transaction::where('customer_id', $c->id)->with('account')->first()->account->code;
        $this->assertSame(ChartOfAccount::PENDAPATAN_INTERNET, $code($hs));
        $this->assertSame(ChartOfAccount::PENDAPATAN_VOUCHER, $code($rs));
    }
}
