<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_health_check_exposes_only_a_success_status(): void
    {
        $response = $this->get('/up')
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8');

        foreach (array_filter([
            config('app.key'),
            config('database.connections.pgsql.password'),
        ]) as $secret) {
            $response->assertDontSee($secret);
        }
    }

    public function test_public_pages_are_available(): void
    {
        foreach (['/', '/about', '/services', '/programs', '/booking', '/contact'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_booking_page_describes_live_private_booking(): void
    {
        $this->get('/booking')
            ->assertOk()
            ->assertSee('Request a private appointment online.')
            ->assertSee('not emergency services');
    }

    public function test_public_branding_is_not_tied_to_a_fixed_location(): void
    {
        foreach (['/', '/about', '/contact', '/login', '/register'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertDontSee('Abuja')
                ->assertDontSee('Abuja, Nigeria');
        }
    }

    public function test_the_site_uses_the_intended_light_form_appearance(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee("window.localStorage.setItem('flux.appearance', 'light');", false);
    }
}
