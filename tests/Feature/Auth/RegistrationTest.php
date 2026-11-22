<?php

namespace Tests\Feature\Auth;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertStatus(200)->assertSee('Daftar Baru')->assertSee('Tambah Tenant');
        $this->get('/register?mode=tenant')->assertStatus(200)->assertSee('Nama Unit Bisnis Baru');
    }

    public function test_new_users_can_register_and_become_owner_of_their_tenant(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'business_name' => 'RTRW Net Satu',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->first();
        $business = Business::where('name', 'RTRW Net Satu')->first();

        $this->assertSame($user->id, (int) $business->owner_id);
        $this->assertSame('owner', $user->roleIn($business->id));
        $this->assertSame($business->id, (int) $user->current_business_id);
    }

    public function test_existing_email_cannot_create_second_account(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'business_name' => 'RTRW Net Dua',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'test@example.com')->count());
    }

    public function test_existing_account_can_add_tenant_and_owns_it(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'password' => 'password']);

        $this->post(route('register.tenant'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'business_name' => 'RTRW Net Cabang',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $business = Business::where('name', 'RTRW Net Cabang')->first();
        $this->assertSame('owner', $user->roleIn($business->id));
        $this->assertSame(1, User::where('email', 'test@example.com')->count());
    }

    public function test_add_tenant_rejects_wrong_password(): void
    {
        User::factory()->create(['email' => 'test@example.com', 'password' => 'password']);

        $this->post(route('register.tenant'), [
            'email' => 'test@example.com',
            'password' => 'salah',
            'business_name' => 'RTRW Net Cabang',
        ])->assertRedirect(route('register', ['mode' => 'tenant']))->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(0, Business::count());
    }
}
