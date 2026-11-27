<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OutletAndDangerZoneTest extends TestCase
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

    private function customer(array $attrs = [], ?Business $business = null): Customer
    {
        $c = new Customer($attrs + ['name' => 'Pelanggan', 'type' => 'pppoe', 'package_fee' => 150000, 'status' => 'active']);
        $c->business_id = ($business ?? $this->business)->id;
        $c->save();

        return $c;
    }

    private function income(Customer $c, float $amount, string $code, ?Business $business = null): Transaction
    {
        $t = new Transaction([
            'date' => now()->toDateString(), 'type' => 'income', 'amount' => $amount, 'description' => 'Setoran',
            'account_id' => ChartOfAccount::byCode($code)->id, 'customer_id' => $c->id,
        ]);
        $t->business_id = ($business ?? $this->business)->id;
        $t->user_id = $this->owner->id;
        $t->save();

        return $t;
    }

    private function member(string $role): User
    {
        $u = User::factory()->create();
        $this->business->users()->attach($u->id, ['role' => $role]);
        $u->switchBusiness($this->business);

        return $u->refresh();
    }

    // ---------- Outlet terpisah dari Pelanggan ----------

    public function test_outlets_have_their_own_menu_and_are_not_listed_as_customers(): void
    {
        $this->customer(['name' => 'Budi PPPoE']);
        $warung = $this->customer(['name' => 'Warung Keysa', 'type' => 'reseller', 'package_fee' => 0]);
        $this->income($warung, 250000, ChartOfAccount::PENDAPATAN_VOUCHER);

        $this->actingAs($this->owner)->get(route('customers.index'))->assertOk()
            ->assertSee('Budi PPPoE')->assertDontSee('Warung Keysa')
            ->assertSee('1 pelanggan terdaftar')
            ->assertSee(route('outlets.index'), false); // menu Outlet

        $this->actingAs($this->owner)->get(route('customers.index', ['type' => 'reseller']))->assertRedirect(route('outlets.index'));

        $this->actingAs($this->owner)->get(route('outlets.index'))->assertOk()
            ->assertSee('Warung Keysa')->assertDontSee('Budi PPPoE')
            ->assertSee('Rp 250.000')
            ->assertSee('+ Outlet');

        $this->actingAs($this->owner)->get(route('customers.show', $warung))->assertOk()->assertSee('← Outlet');
    }

    public function test_outlets_do_not_count_toward_plan_limit_or_get_invoices(): void
    {
        $plan = Plan::create(['name' => 'Kecil', 'price' => 50000, 'max_customers' => 1, 'is_active' => true]);
        $this->business->forceFill(['plan_id' => $plan->id, 'trial_ends_at' => now()->subDay(), 'subscription_ends_at' => now()->addMonth()])->save();

        $this->customer(['name' => 'Outlet A', 'type' => 'reseller', 'package_fee' => 10000]);
        $this->assertSame(0, $this->business->fresh()->customerCount());
        $this->assertTrue($this->business->fresh()->canAddCustomers());

        // Outlet tetap bisa ditambah walau kuota pelanggan penuh
        $this->customer(['name' => 'Pelanggan 1']);
        $this->assertFalse($this->business->fresh()->canAddCustomers());
        $this->actingAs($this->owner)->post(route('customers.store'), [
            'name' => 'Outlet B', 'type' => 'reseller', 'package_fee' => 0, 'billing_type' => 'prepaid', 'billing_cycle' => 'monthly', 'status' => 'active',
        ])->assertRedirect();
        $this->assertNotNull(Customer::where('name', 'Outlet B')->first());

        $stats = (new \App\Services\Billing\InvoiceGenerator($this->business->fresh()))->generate(now(), false);
        $this->assertSame(1, $stats['created']); // hanya Pelanggan 1, outlet tidak ditagih
    }

    // ---------- Reset data ----------

    public function test_owner_resets_selected_categories_only_for_own_business(): void
    {
        $c = $this->customer(['name' => 'Budi']);
        $this->income($c, 150000, ChartOfAccount::PENDAPATAN_INTERNET);

        // Usaha lain tidak boleh tersentuh
        $otherOwner = User::factory()->create();
        $other = Business::registerFor($otherOwner, 'Usaha Lain');
        $oc = $this->customer(['name' => 'Pelanggan Lain'], $other);
        $this->income($oc, 99000, ChartOfAccount::PENDAPATAN_INTERNET, $other);
        $this->owner->switchBusiness($this->business);

        $this->actingAs($this->owner)->get(route('admin.danger.index'))->assertOk()
            ->assertSee('Reset data')->assertSee('Hapus usaha')->assertSee('Keuangan');

        // Nama konfirmasi salah → tidak ada yang terhapus
        $this->actingAs($this->owner)->post(route('admin.danger.reset'), ['categories' => ['finance'], 'confirm_name' => 'salah'])
            ->assertSessionHasErrors('confirm_name');
        $this->assertSame(1, DB::table('transactions')->where('business_id', $this->business->id)->count());

        // Keuangan saja: pelanggan tetap ada
        $this->actingAs($this->owner)->post(route('admin.danger.reset'), ['categories' => ['finance'], 'confirm_name' => 'RTRW Net'])
            ->assertRedirect(route('admin.danger.index'))->assertSessionHas('success');
        $this->assertSame(0, DB::table('transactions')->where('business_id', $this->business->id)->count());
        $this->assertSame(1, DB::table('customers')->where('business_id', $this->business->id)->count());

        // Pelanggan: keuangan otomatis ikut
        $this->income($c, 150000, ChartOfAccount::PENDAPATAN_INTERNET);
        $this->actingAs($this->owner)->post(route('admin.danger.reset'), ['categories' => ['customers'], 'confirm_name' => 'RTRW Net'])
            ->assertSessionHas('success');
        $this->assertSame(0, DB::table('customers')->where('business_id', $this->business->id)->count());
        $this->assertSame(0, DB::table('transactions')->where('business_id', $this->business->id)->count());

        // Usaha lain utuh, usaha & pegawai sendiri tetap ada, tindakan tercatat
        $this->assertSame(1, DB::table('customers')->where('business_id', $other->id)->count());
        $this->assertSame(1, DB::table('transactions')->where('business_id', $other->id)->count());
        $this->assertNotNull($this->business->fresh());
        $this->assertSame('owner', $this->owner->roleIn($this->business->id));
        $this->assertTrue(ActivityLog::withoutGlobalScope('tenant')->where('business_id', $this->business->id)->where('activity', 'like', 'Reset data usaha%')->exists());
    }

    public function test_only_owner_can_open_danger_zone(): void
    {
        $admin = $this->member('admin');
        $this->actingAs($admin)->get(route('admin.danger.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.danger.reset'), ['categories' => ['finance'], 'confirm_name' => 'RTRW Net'])->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.danger.destroy'), ['confirm_name' => 'RTRW Net'])->assertForbidden();
        $this->actingAs($admin)->get(route('dashboard'))->assertDontSee('Reset &amp; Hapus Usaha', false);
        $this->actingAs($this->owner)->get(route('dashboard'))->assertSee('Reset &amp; Hapus Usaha', false);
    }

    // ---------- Hapus usaha (masa tunggu 30 hari) ----------

    public function test_delete_business_locks_it_and_owner_can_cancel_within_grace_period(): void
    {
        $admin = $this->member('admin');
        $this->customer(['name' => 'Budi']);

        $this->actingAs($this->owner)->delete(route('admin.danger.destroy'), ['confirm_name' => 'RTRW Net'])
            ->assertRedirect(route('admin.business.index'))->assertSessionHas('success');

        $biz = $this->business->fresh();
        $this->assertTrue($biz->isPendingDeletion());
        $this->assertEqualsWithDelta(30, now()->diffInDays($biz->deletion_scheduled_at), 1);

        // Terkunci untuk semua anggota
        $this->actingAs($admin->fresh())->get(route('dashboard'))->assertRedirect(route('admin.business.index'));
        $this->actingAs($admin->fresh())->post(route('admin.business.select', $biz))->assertSessionHas('error');
        $this->actingAs($admin->fresh())->get(route('admin.business.index'))->assertSee('Dikunci')->assertDontSee('Batalkan penghapusan');
        $this->actingAs($admin->fresh())->post(route('admin.business.restore', $biz))->assertForbidden();

        // Pemilik membatalkan
        $this->actingAs($this->owner->fresh())->get(route('admin.business.index'))->assertSee('Batalkan penghapusan');
        $this->actingAs($this->owner->fresh())->post(route('admin.business.restore', $biz))->assertSessionHas('success');
        $this->assertFalse($biz->fresh()->isPendingDeletion());
        $this->actingAs($admin->fresh())->post(route('admin.business.select', $biz))->assertRedirect(route('dashboard'));
        $this->assertSame(1, DB::table('customers')->where('business_id', $biz->id)->count());
    }

    public function test_purge_command_deletes_only_expired_businesses_and_keeps_accounts(): void
    {
        $admin = $this->member('admin');
        $c = $this->customer(['name' => 'Budi']);
        $this->income($c, 150000, ChartOfAccount::PENDAPATAN_INTERNET);

        $keep = Business::registerFor(User::factory()->create(), 'Masih Menunggu');
        $keep->scheduleDeletion($this->owner);

        $this->business->scheduleDeletion($this->owner);
        $this->business->forceFill(['deletion_scheduled_at' => now()->subMinute()])->save();

        $this->artisan('netbill:purge-businesses')->assertSuccessful();

        $this->assertNull(Business::find($this->business->id));
        $this->assertSame(0, DB::table('customers')->where('business_id', $this->business->id)->count());
        $this->assertSame(0, DB::table('transactions')->where('business_id', $this->business->id)->count());
        $this->assertSame(0, DB::table('business_user')->where('business_id', $this->business->id)->count());
        $this->assertNotNull($keep->fresh()); // belum lewat masa tunggu

        // Akun tetap ada, tanpa usaha aktif
        $this->assertNotNull($this->owner->fresh());
        $this->assertNull($admin->fresh()->current_business_id);
    }
}
