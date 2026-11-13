<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_public_pages_are_available(): void
    {
        foreach (['/', '/about', '/services', '/programs', '/booking', '/contact'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_booking_page_is_honest_about_phase_two(): void
    {
        $this->get('/booking')
            ->assertOk()
            ->assertSee('Online scheduling is not available yet.')
            ->assertSee('not emergency services');
    }
}
