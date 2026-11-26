<?php

namespace Tests\Feature;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_advantages_and_active_plans(): void
    {
        Plan::query()->update(['is_active' => false]);
        Plan::create(['name' => 'Paket Uji', 'price' => 75000, 'max_customers' => 250, 'is_active' => true, 'sort' => 1]);

        $this->get('/')->assertOk()
            ->assertSee('Keunggulan NetBill')
            ->assertSee('PPPoE, hotspot &amp; voucher terpisah', false)
            ->assertSee('Setoran kolektor dari HP')
            ->assertSee('Paket Uji')->assertSee('Rp 75.000')->assertSee('Maks. 250 pelanggan')
            ->assertSee('Made with dedication by')
            ->assertDontSee('ixradius');
    }
}
