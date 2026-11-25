<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** NetBill SaaS: masa coba, kunci saat berakhir, pembayaran & panel pengelola, batas pelanggan, merek. */
class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-26 10:00:00'));
        Storage::fake('public');
        $this->owner = User::factory()->create();
        $this->business = Business::registerFor($this->owner, 'RTRW Net');
        $this->owner->refresh();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** PNG 1x1 asli (tanpa ekstensi GD). */
    private function png(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('bukti.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));
    }

    private function superadmin(): User
    {
        $u = User::factory()->create();
        Business::registerFor($u, 'Kantor NetBill');
        $u->forceFill(['is_superadmin' => true])->save();

        return $u->refresh();
    }

    public function test_new_business_gets_trial_and_existing_ones_stay_unlimited(): void
    {
        $this->assertSame('trial', $this->business->subscriptionStatus());
        $this->assertSame('2026-12-10', $this->business->trial_ends_at->toDateString());

        $legacy = $this->business->replicate();
        $legacy->forceFill(['trial_ends_at' => null, 'subscription_ends_at' => null]);
        $this->assertSame('unlimited', $legacy->subscriptionStatus());
        $this->assertTrue($legacy->isServiceable());
    }

    public function test_expired_business_is_locked_except_subscription_page(): void
    {
        $kolektor = User::factory()->create();
        $this->business->users()->attach($kolektor->id, ['role' => 'kolektor']);
        $kolektor->switchBusiness($this->business);

        Carbon::setTestNow(Carbon::parse('2026-12-11 08:00:00')); // masa coba lewat

        $this->actingAs($this->owner)->get(route('dashboard'))->assertRedirect(route('subscription.index'));
        $this->actingAs($this->owner)->get(route('customers.index'))->assertRedirect(route('subscription.index'));
        $this->actingAs($this->owner)->get(route('subscription.index'))->assertOk()->assertSee('Berakhir')->assertSee('Kirim bukti pembayaran');
        $this->actingAs($kolektor->refresh())->get(route('mobile.home'))->assertRedirect(route('subscription.index'));
        $this->actingAs($kolektor)->get(route('subscription.index'))->assertOk()->assertSee('dilakukan oleh pemilik usaha');

        // Tugas otomatis melewati usaha yang terkunci
        $this->artisan('billing:generate-invoices')->assertSuccessful();
    }

    public function test_owner_pays_and_superadmin_confirms(): void
    {
        $pro = Plan::where('name', 'Pro')->firstOrFail();

        $this->actingAs($this->owner)->post(route('subscription.store'), [
            'plan_id' => $pro->id, 'months' => 3, 'proof_image' => $this->png(),
        ])->assertSessionHas('success');

        $payment = SubscriptionPayment::firstOrFail();
        $this->assertSame('pending', $payment->status);
        $this->assertEquals(300000, $payment->amount);

        // Belum boleh kirim lagi selama masih menunggu
        $this->actingAs($this->owner)->post(route('subscription.store'), [
            'plan_id' => $pro->id, 'months' => 1, 'proof_image' => $this->png(),
        ])->assertSessionHas('error');

        $admin = $this->superadmin();
        $this->actingAs($admin)->get(route('platform.payments.index'))->assertOk()->assertSee('RTRW Net')->assertSee('Rp 300.000');
        $this->actingAs($admin)->post(route('platform.payments.confirm', $payment))->assertSessionHas('success');

        $b = $this->business->fresh();
        $this->assertSame('active', $b->subscriptionStatus());
        $this->assertSame($pro->id, $b->plan_id);
        $this->assertSame('2027-02-25', $b->subscription_ends_at->toDateString()); // 26 Nov + 3 bulan − 1 hari
        $this->assertSame('confirmed', $payment->fresh()->status);

        // Perpanjang lagi saat masih aktif: menyambung dari tanggal berakhir
        $this->actingAs($admin)->post(route('platform.tenants.extend', $b), ['plan_id' => $pro->id, 'months' => 1]);
        $this->assertSame('2027-03-25', $b->fresh()->subscription_ends_at->toDateString());
    }

    public function test_non_superadmin_cannot_open_platform_and_staff_cannot_pay(): void
    {
        $this->actingAs($this->owner)->get(route('platform.tenants.index'))->assertNotFound();

        $admin = User::factory()->create();
        $this->business->users()->attach($admin->id, ['role' => 'admin']);
        $admin->switchBusiness($this->business);
        $this->actingAs($admin->refresh())->post(route('subscription.store'), [
            'plan_id' => Plan::first()->id, 'months' => 1, 'proof_image' => $this->png(),
        ])->assertForbidden();
    }

    public function test_superadmin_panel_suspend_resume_and_plans(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->get(route('platform.tenants.index'))->assertOk()->assertSee('RTRW Net')->assertSee('Masa coba');
        $this->actingAs($admin)->get(route('platform.tenants.show', $this->business))->assertOk();

        $this->actingAs($admin)->post(route('platform.tenants.suspend', $this->business), ['suspend_reason' => 'Penipuan'])->assertSessionHas('success');
        $this->assertSame('suspended', $this->business->fresh()->subscriptionStatus());
        $this->actingAs($this->owner)->get(route('dashboard'))->assertRedirect(route('subscription.index'));

        $this->actingAs($admin)->post(route('platform.tenants.resume', $this->business));
        $this->assertSame('trial', $this->business->fresh()->subscriptionStatus());

        $this->actingAs($admin)->post(route('platform.plans.store'), ['name' => 'Mini', 'price' => 25000, 'max_customers' => 30, 'is_active' => 1]);
        $this->assertDatabaseHas('plans', ['name' => 'Mini', 'max_customers' => 30]);
        $this->actingAs($admin)->put(route('platform.settings.update'), ['payment_instructions' => 'BCA 123 a.n. CV']);
        $this->actingAs($this->owner)->get(route('subscription.index'))->assertSee('BCA 123 a.n. CV')->assertSee('Mini');

        $this->artisan('netbill:superadmin', ['email' => $this->owner->email])->assertSuccessful();
        $this->assertTrue($this->owner->fresh()->is_superadmin);
    }

    public function test_customer_limit_of_plan_is_enforced(): void
    {
        $plan = Plan::create(['name' => 'Kecil', 'price' => 10000, 'max_customers' => 1, 'is_active' => true]);
        $this->business->extendSubscription(1, $plan);
        $this->actingAs($this->owner);

        Customer::create(['name' => 'A', 'type' => 'pppoe', 'package_fee' => 100000, 'status' => 'active']);
        $this->assertFalse($this->business->fresh()->canAddCustomers());

        $this->post(route('customers.store'), ['name' => 'B', 'type' => 'pppoe', 'package_fee' => 100000])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'batas paket'));
        $this->assertSame(1, Customer::count());

        // Paket yang lebih kecil dari jumlah pelanggan tidak bisa dipilih
        Customer::create(['name' => 'C', 'type' => 'pppoe', 'package_fee' => 1, 'status' => 'active']);
        $this->post(route('subscription.store'), ['plan_id' => $plan->id, 'months' => 1, 'proof_image' => $this->png()])
            ->assertSessionHas('error');
    }

    public function test_branding_and_legal_footer(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('NetBill')->assertSee('CV. Parameter Digital Inovation')->assertDontSee('Parameter Fintech');
        $this->actingAs($this->owner)->get(route('dashboard'))->assertOk()
            ->assertSee('Made with dedication by', false)->assertSee('CV. Parameter Digital Inovation')->assertSee('Langganan NetBill');
        $this->actingAs($this->owner)->get(route('mobile.home'))->assertOk()->assertSee('CV. Parameter Digital Inovation');

        // Pengingat 7 hari sebelum masa coba berakhir
        Carbon::setTestNow(Carbon::parse('2026-12-05 08:00:00'));
        $this->actingAs($this->owner)->get(route('dashboard'))->assertSee('Masa coba NetBill berakhir dalam 5 hari');
    }
}
