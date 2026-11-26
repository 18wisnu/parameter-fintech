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
        $this->actingAs($this->owner)->get(route('subscription.index'))->assertOk()->assertSee('Berakhir')->assertSee('Pilih Pro');
        $this->actingAs($kolektor->refresh())->get(route('mobile.home'))->assertRedirect(route('subscription.index'));
        $this->actingAs($kolektor)->get(route('subscription.index'))->assertOk()->assertSee('dilakukan oleh pemilik atau admin usaha');

        // Tugas otomatis melewati usaha yang terkunci
        $this->artisan('billing:generate-invoices')->assertSuccessful();
    }

    public function test_checkout_order_payment_and_confirmation(): void
    {
        $pro = Plan::where('name', 'Pro')->firstOrFail();
        $this->actingAs($this->owner);

        $this->get(route('subscription.index'))->assertOk()->assertSee('Pilih Pro');
        $this->get(route('subscription.checkout', ['plan' => $pro->id, 'months' => 3]))->assertOk()
            ->assertSee('Ringkasan pesanan')->assertSee('Transfer bank')->assertSee('Segera hadir');

        // Gateway belum dipasang → metode itu ditolak
        $this->post(route('subscription.place'), ['plan_id' => $pro->id, 'months' => 3, 'method' => 'gateway'])->assertSessionHasErrors('method');

        $this->post(route('subscription.place'), ['plan_id' => $pro->id, 'months' => 3, 'method' => 'manual_transfer'])
            ->assertRedirect();
        $order = SubscriptionPayment::firstOrFail();
        $this->assertSame('awaiting_payment', $order->status);
        $this->assertEquals(300000, $order->amount);
        $this->assertSame('NB261126-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT), $order->reference);

        // Pesanan terbuka: checkout baru diarahkan ke pesanan yang ada
        $this->get(route('subscription.checkout'))->assertRedirect(route('subscription.order', $order));
        $this->get(route('subscription.order', $order))->assertOk()->assertSee($order->reference)->assertSee('Kirim bukti pembayaran')->assertSee('Rp 300.000');

        $this->post(route('subscription.proof', $order), ['proof_image' => $this->png(), 'notes' => 'BCA a.n. Budi'])->assertSessionHas('success');
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->proof_image);

        $admin = $this->superadmin();
        $this->actingAs($admin)->get(route('platform.payments.index'))->assertOk()->assertSee('RTRW Net')->assertSee($order->reference);
        $this->actingAs($admin)->post(route('platform.payments.confirm', $order))->assertSessionHas('success');

        $b = $this->business->fresh();
        $this->assertSame('active', $b->subscriptionStatus());
        $this->assertSame($pro->id, $b->plan_id);
        $this->assertSame('2027-02-25', $b->subscription_ends_at->toDateString()); // 26 Nov + 3 bulan − 1 hari
        $this->actingAs($this->owner)->get(route('subscription.order', $order))->assertSee('Pembayaran lunas');

        // Perpanjang lagi saat masih aktif: menyambung dari tanggal berakhir
        $this->actingAs($admin)->post(route('platform.tenants.extend', $b), ['plan_id' => $pro->id, 'months' => 1]);
        $this->assertSame('2027-03-25', $b->fresh()->subscription_ends_at->toDateString());
    }

    public function test_order_can_be_cancelled_and_expires(): void
    {
        $this->actingAs($this->owner)->post(route('subscription.place'), ['plan_id' => Plan::first()->id, 'months' => 1, 'method' => 'manual_transfer']);
        $order = SubscriptionPayment::firstOrFail();
        $this->post(route('subscription.cancel', $order))->assertRedirect(route('subscription.index'));
        $this->assertSame('cancelled', $order->fresh()->status);

        $this->post(route('subscription.place'), ['plan_id' => Plan::first()->id, 'months' => 1, 'method' => 'manual_transfer']);
        $second = SubscriptionPayment::latest('id')->first();
        Carbon::setTestNow(now()->addHours(73));
        $this->get(route('subscription.checkout'))->assertOk(); // pesanan kedaluwarsa dibatalkan otomatis
        $this->assertSame('cancelled', $second->fresh()->status);
        $this->post(route('subscription.proof', $second), ['proof_image' => $this->png()])->assertStatus(422);

        // Pesanan usaha lain tidak bisa dibuka
        $other = User::factory()->create();
        Business::registerFor($other, 'Lain');
        $this->actingAs($other->refresh())->get(route('subscription.order', $second))->assertNotFound();
    }

    public function test_non_superadmin_cannot_open_platform_and_staff_cannot_pay(): void
    {
        $this->actingAs($this->owner)->get(route('platform.tenants.index'))->assertNotFound();

        $admin = User::factory()->create();
        $this->business->users()->attach($admin->id, ['role' => 'admin']);
        $admin->switchBusiness($this->business);
        // Admin boleh memperpanjang langganan seperti pemilik
        $this->actingAs($admin->refresh())->get(route('subscription.checkout'))->assertOk();

        $teknisi = User::factory()->create();
        $this->business->users()->attach($teknisi->id, ['role' => 'teknisi']);
        $teknisi->switchBusiness($this->business);
        $this->actingAs($teknisi->refresh())->post(route('subscription.place'), [
            'plan_id' => Plan::first()->id, 'months' => 1, 'method' => 'manual_transfer',
        ])->assertStatus(403);
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
        $this->actingAs($this->owner)->get(route('subscription.index'))->assertSee('Mini');
        $this->post(route('subscription.place'), ['plan_id' => Plan::where('name', 'Mini')->value('id'), 'months' => 1, 'method' => 'manual_transfer']);
        $this->get(route('subscription.order', SubscriptionPayment::firstOrFail()))->assertSee('BCA 123 a.n. CV');

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
        $this->post(route('subscription.place'), ['plan_id' => $plan->id, 'months' => 1, 'method' => 'manual_transfer'])
            ->assertSessionHas('error');
        $this->assertSame(0, SubscriptionPayment::count());
    }

    public function test_platform_customer_table_and_csv(): void
    {
        $admin = $this->superadmin();
        $this->actingAs($this->owner);
        Customer::create(['name' => 'Pelanggan Rahasia', 'type' => 'pppoe', 'package_fee' => 1, 'status' => 'active']);

        $this->actingAs($admin)->get(route('platform.customers.index'))->assertOk()
            ->assertSee($this->owner->email)->assertSee('RTRW Net')->assertSee('Masa coba')
            ->assertDontSee('Pelanggan Rahasia'); // data pelanggan internet milik usaha tidak dibuka

        $this->actingAs($admin)->get(route('platform.customers.index', ['q' => 'tidak-ada-xyz']))->assertOk()->assertSee('Belum ada pelanggan');

        $csv = $this->actingAs($admin)->get(route('platform.customers.index', ['export' => 'csv']))->assertOk()->getContent();
        $this->assertStringContainsString($this->owner->email, $csv);
        $this->assertStringContainsString('RTRW Net', $csv);

        $this->actingAs($this->owner)->get(route('platform.customers.index'))->assertNotFound();
    }

    public function test_profit_sharing_recipients_can_be_managed_from_report_page(): void
    {
        $this->actingAs($this->owner);
        $this->get(route('reports.index'))->assertOk()->assertSee('Penerima bagi hasil');

        $this->post(route('admin.profit_sharing.stakeholder.store'), ['name' => 'Investor A', 'percentage' => 60])->assertSessionHas('success');
        $this->post(route('admin.profit_sharing.stakeholder.store'), ['name' => 'Pengelola', 'percentage' => 50])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Maksimal 100%') && str_contains($m, 'sisa yang tersedia 40%'));
        $this->post(route('admin.profit_sharing.stakeholder.store'), ['name' => 'Pengelola', 'percentage' => 40])->assertSessionHas('success');

        $a = \App\Models\ProfitSharingStakeholder::where('name', 'Investor A')->firstOrFail();
        $this->put(route('admin.profit_sharing.stakeholder.update', $a->id), ['name' => 'Investor Utama', 'percentage' => 70])->assertSessionHas('error');
        $this->put(route('admin.profit_sharing.stakeholder.update', $a->id), ['name' => 'Investor Utama', 'percentage' => 55])->assertSessionHas('success');
        $this->assertEquals(55, $a->fresh()->percentage);

        $this->get(route('reports.index'))->assertSee('Investor Utama')->assertSee('Total 95%');
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
