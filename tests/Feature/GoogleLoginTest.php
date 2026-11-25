<?php

namespace Tests\Feature;

use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
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
}
