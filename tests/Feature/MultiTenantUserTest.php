<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MultiTenantUserTest extends TestCase
{
    use RefreshDatabase;

    private function makeOwnerWithBusiness(string $email, string $businessName): array
    {
        $owner = User::factory()->create(['email' => $email]);
        $business = Business::registerFor($owner, $businessName);

        return [$owner->fresh(), $business];
    }

    public function test_existing_email_is_linked_to_another_tenant_instead_of_rejected(): void
    {
        [$ownerA, $bizA] = $this->makeOwnerWithBusiness('a@test.com', 'Bisnis A');
        [$ownerB, $bizB] = $this->makeOwnerWithBusiness('b@test.com', 'Bisnis B');

        $this->actingAs($ownerA)->post(route('admin.users.store'), [
            'name' => 'Budi', 'email' => 'budi@test.com', 'password' => 'secret123', 'role' => 'teknisi', 'base_salary' => 1000000,
        ])->assertSessionHas('success');

        // Email yang sama, tanpa password, di tenant lain dengan role & gaji berbeda
        $this->actingAs($ownerB)->post(route('admin.users.store'), [
            'name' => 'Budi', 'email' => 'BUDI@test.com', 'role' => 'kolektor', 'base_salary' => 750000,
        ])->assertSessionHas('success');

        $budi = User::where('email', 'budi@test.com')->first();
        $this->assertSame(1, User::where('email', 'budi@test.com')->count());
        $this->assertSame('teknisi', $budi->roleIn($bizA->id));
        $this->assertSame('kolektor', $budi->roleIn($bizB->id));

        $this->assertEquals(1000000, User::membersOf($bizA->id)->find($budi->id)->base_salary);
        $this->assertEquals(750000, User::membersOf($bizB->id)->find($budi->id)->base_salary);

        // Menambahkan lagi ke tenant yang sama ditolak
        $this->actingAs($ownerB)->post(route('admin.users.store'), [
            'name' => 'Budi', 'email' => 'budi@test.com', 'role' => 'admin',
        ])->assertSessionHas('error');
    }

    public function test_one_account_with_three_different_roles_and_salaries(): void
    {
        // Skenario: saya pemilik di tenant 1, admin di tenant 2, kolektor di tenant 3
        [$me, $biz1] = $this->makeOwnerWithBusiness('saya@test.com', 'Tenant 1');
        [$owner2, $biz2] = $this->makeOwnerWithBusiness('o2@test.com', 'Tenant 2');
        [$owner3, $biz3] = $this->makeOwnerWithBusiness('o3@test.com', 'Tenant 3');

        $this->actingAs($owner2)->post(route('admin.users.store'), [
            'name' => 'Saya', 'email' => 'saya@test.com', 'role' => 'admin', 'base_salary' => 3000000,
        ])->assertSessionHas('success');
        $this->actingAs($owner3)->post(route('admin.users.store'), [
            'name' => 'Saya', 'email' => 'saya@test.com', 'role' => 'kolektor', 'base_salary' => 1500000,
        ])->assertSessionHas('success');

        $me->refresh();
        $this->assertSame(['owner', 'admin', 'kolektor'], [
            $me->roleIn($biz1->id), $me->roleIn($biz2->id), $me->roleIn($biz3->id),
        ]);

        // Pindah tenant: role ikut berpindah
        $this->actingAs($me)->post(route('admin.business.select', $biz3))->assertRedirect(route('mobile.home'));
        $this->assertSame('kolektor', $me->fresh()->role);

        // Sebagai kolektor di tenant 3, menu pegawai tidak boleh diakses
        $this->actingAs($me->fresh())->get(route('admin.users.index'))->assertRedirect(route('mobile.home'));

        $this->actingAs($me)->post(route('admin.business.select', $biz2))->assertRedirect(route('dashboard'));
        $this->assertSame('admin', $me->fresh()->role);
        $this->actingAs($me->fresh())->get(route('admin.users.index'))->assertOk();
        // Admin tidak boleh membuka menu khusus owner
        $this->actingAs($me->fresh())->get(route('admin.mixradius.edit'))->assertForbidden();

        // Ubah gaji di tenant 2 tidak mengubah gaji di tenant 3
        $this->actingAs($owner2)->patch(route('admin.salaries.update-base', $me->id), ['base_salary' => 3500000]);
        $this->assertEquals(3500000, User::membersOf($biz2->id)->find($me->id)->base_salary);
        $this->assertEquals(1500000, User::membersOf($biz3->id)->find($me->id)->base_salary);
    }

    public function test_owner_cannot_be_changed_or_removed_and_owner_role_cannot_be_assigned(): void
    {
        [$ownerA, $bizA] = $this->makeOwnerWithBusiness('a@test.com', 'Bisnis A');

        $admin = User::factory()->create(['email' => 'adm@test.com']);
        $admin->businesses()->attach($bizA->id, ['role' => 'admin']);
        $admin->switchBusiness($bizA);

        $this->actingAs($admin)->put(route('admin.users.updateRole', $ownerA), ['role' => 'kolektor'])->assertSessionHas('error');
        $this->actingAs($admin)->delete(route('admin.users.destroy', $ownerA))->assertSessionHas('error');
        $this->assertSame('owner', $ownerA->roleIn($bizA->id));

        $this->actingAs($ownerA)->post(route('admin.users.store'), [
            'name' => 'X', 'email' => 'x@test.com', 'password' => 'secret123', 'role' => 'owner',
        ])->assertSessionHasErrors('role');
    }

    public function test_cannot_manage_members_of_other_tenant(): void
    {
        [$ownerA, $bizA] = $this->makeOwnerWithBusiness('a@test.com', 'Bisnis A');
        [$ownerB, $bizB] = $this->makeOwnerWithBusiness('b@test.com', 'Bisnis B');

        $this->actingAs($ownerB)
            ->put(route('admin.users.updateRole', $ownerA), ['role' => 'kolektor'])
            ->assertForbidden();

        $this->assertSame([$ownerA->id], User::inBusiness($bizA->id)->pluck('id')->all());
    }

    public function test_shared_account_credentials_can_only_be_changed_by_account_holder(): void
    {
        [$ownerA, $bizA] = $this->makeOwnerWithBusiness('a@test.com', 'Bisnis A');
        [$ownerB, $bizB] = $this->makeOwnerWithBusiness('b@test.com', 'Bisnis B');

        $staff = User::factory()->create(['email' => 's@test.com', 'password' => 'lama1234']);
        $staff->businesses()->attach($bizA->id, ['role' => 'teknisi']);
        $staff->businesses()->attach($bizB->id, ['role' => 'kolektor']);

        $this->actingAs($ownerB)->put(route('admin.users.update', $staff), [
            'name' => 'Diambil', 'email' => 'hijack@test.com', 'password' => 'baru1234', 'role' => 'admin', 'base_salary' => 5,
        ])->assertSessionHas('success');

        $staff->refresh();
        $this->assertSame('s@test.com', $staff->email);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('lama1234', $staff->password));
        $this->assertSame('admin', $staff->roleIn($bizB->id));
        $this->assertSame('teknisi', $staff->roleIn($bizA->id));
    }

    public function test_destroy_only_detaches_when_user_belongs_to_other_tenants(): void
    {
        [$ownerA, $bizA] = $this->makeOwnerWithBusiness('a@test.com', 'Bisnis A');
        [$ownerB, $bizB] = $this->makeOwnerWithBusiness('b@test.com', 'Bisnis B');

        $staff = User::factory()->create(['email' => 's@test.com']);
        $staff->businesses()->attach($bizA->id, ['role' => 'teknisi']);
        $staff->businesses()->attach($bizB->id, ['role' => 'kolektor']);
        $staff->switchBusiness($bizA);

        $this->actingAs($ownerA)->delete(route('admin.users.destroy', $staff))->assertSessionHas('success');

        $staff->refresh();
        $this->assertFalse($staff->belongsToBusiness($bizA->id));
        $this->assertTrue($staff->belongsToBusiness($bizB->id));
        $this->assertSame($bizB->id, (int) $staff->current_business_id);
        $this->assertSame('kolektor', $staff->role);
    }

    public function test_removed_user_loses_access_to_old_tenant(): void
    {
        [$ownerA, $bizA] = $this->makeOwnerWithBusiness('a@test.com', 'Bisnis A');

        $staff = User::factory()->create(['email' => 's@test.com', 'current_business_id' => $bizA->id]);
        $this->assertFalse($staff->ensureCurrentBusiness());
        $this->assertNull($staff->fresh()->current_business_id);
    }

    public function test_mixradius_settings_are_per_tenant_encrypted_and_testable(): void
    {
        [$ownerA, $bizA] = $this->makeOwnerWithBusiness('a@test.com', 'Bisnis A');
        [$ownerB, $bizB] = $this->makeOwnerWithBusiness('b@test.com', 'Bisnis B');

        $this->actingAs($ownerA)->put(route('admin.mixradius.update'), [
            'mixradius_enabled' => '1',
            'mixradius_base_url' => 'https://mixradius-a.test',
            'mixradius_api_key' => 'key-a',
            'mixradius_api_secret' => 'secret-a',
        ])->assertSessionHas('success');

        $raw = DB::table('businesses')->where('id', $bizA->id)->value('mixradius_api_key');
        $this->assertNotSame('key-a', $raw);
        $this->assertSame('key-a', $bizA->fresh()->mixradius_api_key);
        $this->assertNull($bizB->fresh()->mixradius_base_url);

        // Simpan ulang dengan key kosong: key lama tetap
        $this->actingAs($ownerA)->put(route('admin.mixradius.update'), [
            'mixradius_enabled' => '1',
            'mixradius_base_url' => 'https://mixradius-a.test',
        ]);
        $this->assertSame('key-a', $bizA->fresh()->mixradius_api_key);

        Http::fake(['mixradius-a.test/*' => Http::response(['success' => true, 'data' => ['items' => [], 'total' => 6, 'total_pages' => 1]], 200)]);

        $this->actingAs($ownerA)->post(route('admin.mixradius.test'))->assertSessionHas('success');

        $this->actingAs($ownerA)->get(route('admin.mixradius.edit'))
            ->assertOk()->assertSee('https://mixradius-a.test')->assertDontSee('key-a');
        $this->actingAs($ownerA)->get(route('admin.salaries.index'))->assertOk();

        Http::assertSent(fn ($request) => $request->hasHeader('X-API-Key', 'key-a')
            && $request->hasHeader('X-API-Secret', 'secret-a')
            && str_starts_with($request->url(), 'https://mixradius-a.test/api/v1/products/catalog'));
    }
}
