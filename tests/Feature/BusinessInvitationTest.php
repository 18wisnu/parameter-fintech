<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function owner(string $email, string $businessName): array
    {
        $owner = User::factory()->create(['email' => $email, 'name' => ucfirst(strstr($email, '@', true))]);
        $business = Business::registerFor($owner, $businessName);

        return [$owner->fresh(), $business];
    }

    private function invite(User $by, string $email, string $role = 'admin')
    {
        return $this->actingAs($by)->post(route('admin.users.store'), ['name' => 'X', 'email' => $email, 'role' => $role]);
    }

    public function test_invitee_sees_invitation_and_joins_only_after_accepting(): void
    {
        [$wisnu, $bizW] = $this->owner('wisnu@test.com', 'Wisnu Net');
        [$budi, $bizB] = $this->owner('budi@test.com', 'Budi Net');

        $this->invite($budi, 'Wisnu@Test.com')->assertSessionHas('success');
        $this->assertNull($wisnu->roleIn($bizB->id));

        // Pengundang melihat undangan yang menunggu di menu Pegawai
        $this->actingAs($budi)->get(route('admin.users.index'))
            ->assertSee('Undangan menunggu jawaban')->assertSee('wisnu@test.com');

        // Penerima melihat pengingat di dashboard dan daftar undangan di halaman Pilih usaha
        $this->actingAs($wisnu)->get(route('dashboard'))->assertSee('1 undangan bergabung');
        $this->actingAs($wisnu)->get(route('admin.business.index'))
            ->assertSee('Undangan bergabung')->assertSee('Budi Net')->assertSee('Admin')->assertSee('Terima');

        $inv = BusinessInvitation::first();
        $this->actingAs($wisnu)->post(route('admin.business.invitations.accept', $inv))
            ->assertRedirect(route('admin.business.index'))->assertSessionHas('success');

        $wisnu->refresh();
        $this->assertSame('admin', $wisnu->roleIn($bizB->id));
        $this->assertSame('owner', $wisnu->roleIn($bizW->id));
        $this->assertSame($bizW->id, (int) $wisnu->current_business_id); // tetap di usahanya sendiri
        $this->assertSame('accepted', $inv->fresh()->status);

        // Setelah diterima, pengingat hilang
        $this->actingAs($wisnu)->get(route('dashboard'))->assertDontSee('undangan bergabung');

        // Masuk ke usaha Budi sebagai admin
        $this->actingAs($wisnu)->post(route('admin.business.select', $bizB))->assertRedirect(route('dashboard'));
        $this->assertSame('admin', $wisnu->fresh()->role);
    }

    public function test_invitee_can_decline(): void
    {
        [$wisnu] = $this->owner('wisnu@test.com', 'Wisnu Net');
        [$budi, $bizB] = $this->owner('budi@test.com', 'Budi Net');

        $this->invite($budi, 'wisnu@test.com');
        $inv = BusinessInvitation::first();

        $this->actingAs($wisnu)->post(route('admin.business.invitations.decline', $inv))->assertSessionHas('success');
        $this->assertSame('declined', $inv->fresh()->status);
        $this->assertNull($wisnu->roleIn($bizB->id));

        // Sudah dijawab: tidak bisa diterima lagi, tapi bisa diundang ulang
        $this->actingAs($wisnu)->post(route('admin.business.invitations.accept', $inv))->assertStatus(410);
        $this->invite($budi, 'wisnu@test.com')->assertSessionHas('success');
    }

    public function test_only_the_invited_account_can_accept(): void
    {
        $this->owner('wisnu@test.com', 'Wisnu Net');
        [$budi, $bizB] = $this->owner('budi@test.com', 'Budi Net');
        [$orangLain] = $this->owner('lain@test.com', 'Lain Net');

        $this->invite($budi, 'wisnu@test.com');
        $inv = BusinessInvitation::first();

        $this->actingAs($orangLain)->post(route('admin.business.invitations.accept', $inv))->assertForbidden();
        $this->assertNull($orangLain->roleIn($bizB->id));
        $this->actingAs($orangLain)->get(route('admin.business.index'))->assertDontSee('Budi Net');
    }

    public function test_inviting_business_can_cancel_but_other_business_cannot(): void
    {
        [$wisnu] = $this->owner('wisnu@test.com', 'Wisnu Net');
        [$budi] = $this->owner('budi@test.com', 'Budi Net');
        [$orangLain] = $this->owner('lain@test.com', 'Lain Net');

        $this->invite($budi, 'wisnu@test.com');
        $inv = BusinessInvitation::first();

        $this->actingAs($orangLain)->delete(route('admin.invitations.cancel', $inv))->assertForbidden();
        $this->actingAs($budi)->delete(route('admin.invitations.cancel', $inv))->assertSessionHas('success');
        $this->assertSame('cancelled', $inv->fresh()->status);

        $this->actingAs($wisnu)->post(route('admin.business.invitations.accept', $inv))->assertStatus(410);
    }

    public function test_new_email_is_still_created_directly_as_staff(): void
    {
        [$budi, $bizB] = $this->owner('budi@test.com', 'Budi Net');

        $this->actingAs($budi)->post(route('admin.users.store'), [
            'name' => 'Teknisi Baru', 'email' => 'tek@test.com', 'password' => 'rahasia1', 'role' => 'teknisi',
        ])->assertSessionHas('success');

        $this->assertSame('teknisi', User::where('email', 'tek@test.com')->first()->roleIn($bizB->id));
        $this->assertSame(0, BusinessInvitation::count());
    }
}
