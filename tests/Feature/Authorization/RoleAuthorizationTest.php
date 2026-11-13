<?php

namespace Tests\Feature\Authorization;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_are_redirected_to_the_dashboard_for_their_role(): void
    {
        $cases = [
            [User::factory()->client()->create(), '/client/dashboard'],
            [User::factory()->counsellor()->create(), '/counsellor/dashboard'],
            [User::factory()->administrator()->create(), '/admin/dashboard'],
        ];

        foreach ($cases as [$user, $expectedPath]) {
            $this->actingAs($user)->get('/dashboard')->assertRedirect($expectedPath);
        }
    }

    public function test_each_role_is_blocked_from_other_dashboards(): void
    {
        $client = User::factory()->client()->create();
        $counsellor = User::factory()->counsellor()->create();
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($client)->get('/counsellor/dashboard')->assertForbidden();
        $this->actingAs($client)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($counsellor)->get('/client/dashboard')->assertForbidden();
        $this->actingAs($administrator)->get('/client/dashboard')->assertForbidden();
    }

    public function test_only_administrators_can_view_enquiries(): void
    {
        $enquiry = Enquiry::factory()->create();

        $this->actingAs(User::factory()->client()->create())
            ->get('/admin/enquiries')
            ->assertForbidden();

        $this->actingAs(User::factory()->administrator()->create())
            ->get('/admin/enquiries')
            ->assertOk()
            ->assertSee($enquiry->email);
    }
}
