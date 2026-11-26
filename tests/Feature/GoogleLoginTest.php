<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_always_shows_account_chooser(): void
    {
        config(['services.google' => ['client_id' => 'x', 'client_secret' => 'y', 'redirect' => 'https://fintech.test/auth/google/callback']]);

        $location = $this->get(route('auth.google'))->assertRedirect()->headers->get('Location');

        $this->assertStringContainsString('accounts.google.com', $location);
        $this->assertStringContainsString('prompt=select_account', $location);
        $this->assertStringContainsString(urlencode('https://fintech.test/auth/google/callback'), $location);
    }

    public function test_cancelled_google_login_shows_message(): void
    {
        config(['services.google' => ['client_id' => 'x', 'client_secret' => 'y', 'redirect' => 'https://fintech.test/auth/google/callback']]);

        $this->get('/auth/google/callback?error=access_denied')
            ->assertRedirect('/login')->assertSessionHas('error', 'Login dengan Google dibatalkan.');
    }

    private function fakeGoogleUser(string $email, string $name = 'Calon Pelanggan'): void
    {
        $googleUser = (new GoogleUser())->map(['id' => 'g-' . md5($email), 'name' => $name, 'email' => $email, 'avatar' => null]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_unregistered_google_email_gets_new_account_and_is_sent_to_create_business(): void
    {
        Business::registerFor(User::factory()->create(), 'Usaha Lain'); // sudah ada tenant lain di platform

        $this->fakeGoogleUser('Baru@Gmail.com');

        $this->get('/auth/google/callback?code=abc')
            ->assertRedirect(route('admin.business.index'))
            ->assertSessionHas('success');

        $user = User::where('email', 'baru@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Calon Pelanggan', $user->name);
        $this->assertNotNull($user->google_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame(0, $user->businesses()->count()); // tidak otomatis masuk ke usaha orang lain

        $this->get(route('admin.business.index'))->assertOk()
            ->assertSee('Akun Anda berhasil dibuat')
            ->assertSee('Buat usaha')
            ->assertDontSee('Usaha Lain');

        $this->post(route('admin.business.store'), ['name' => 'Net Baru'])->assertRedirect();
        $user->refresh();
        $this->assertSame('owner', $user->roleIn($user->current_business_id));
    }

    public function test_registered_google_email_logs_in_without_creating_account(): void
    {
        $business = Business::registerFor(User::factory()->create(), 'Net Lama');
        $user = User::factory()->create(['email' => 'lama@gmail.com', 'role' => 'admin']);
        $user->businesses()->attach($business->id, ['role' => 'admin']);

        $this->fakeGoogleUser('lama@gmail.com');

        $this->get('/auth/google/callback?code=abc')->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::where('email', 'lama@gmail.com')->count());
    }

    public function test_login_page_shows_error_message(): void
    {
        $this->withSession(['error' => 'Gagal login dengan Google. Silakan coba lagi.'])
            ->get('/login')->assertOk()->assertSee('Gagal login dengan Google. Silakan coba lagi.');
    }
}
