<?php

namespace Tests\Feature\Enquiries;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use Tests\TestCase;

class EnquirySubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        RateLimiter::clear('enquiry:'.hash('sha256', '127.0.0.1|person@example.com'));

        parent::tearDown();
    }

    public function test_a_valid_general_enquiry_is_persisted(): void
    {
        Volt::test('contact')
            ->set('name', 'Test Person')
            ->set('email', 'PERSON@EXAMPLE.COM')
            ->set('message', 'I would like general information about individual counselling.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('enquiries', [
            'name' => 'Test Person',
            'email' => 'person@example.com',
        ]);
    }

    public function test_enquiry_fields_are_validated(): void
    {
        Volt::test('contact')
            ->set('name', '')
            ->set('email', 'not-an-email')
            ->set('message', 'short')
            ->call('submit')
            ->assertHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_enquiry_submissions_are_rate_limited(): void
    {
        $component = Volt::test('contact');

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $component
                ->set('name', 'Test Person')
                ->set('email', 'person@example.com')
                ->set('message', 'A general enquiry that is long enough to be accepted.')
                ->call('submit')
                ->assertHasNoErrors();
        }

        $component
            ->set('name', 'Test Person')
            ->set('email', 'person@example.com')
            ->set('message', 'A fourth general enquiry should be rejected by the limiter.')
            ->call('submit')
            ->assertHasErrors(['message']);

        $this->assertDatabaseCount('enquiries', 3);
    }
}
