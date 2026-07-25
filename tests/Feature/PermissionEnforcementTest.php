<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_owner_can_reach_every_gated_route(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->get(route('product'))->assertOk();
        $this->actingAs($owner)->get(route('invoice'))->assertOk();
        // Merged into the product page - this route now just redirects there
        // (still permission-gated, so still worth asserting it's reachable).
        $this->actingAs($owner)->get(route('invoice.inventrylist'))->assertRedirect(route('product'));
        $this->actingAs($owner)->get(route('invoice.vender'))->assertOk();
        $this->actingAs($owner)->get(route('invoice.histry'))->assertOk();
        $this->actingAs($owner)->get(route('listqutation'))->assertOk();
    }

    public function test_worker_with_no_permissions_is_blocked_from_every_gated_route(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $this->actingAs($worker)->get(route('product'))->assertStatus(403);
        $this->actingAs($worker)->get(route('invoice'))->assertStatus(403);
        $this->actingAs($worker)->get(route('invoice.inventrylist'))->assertStatus(403);
        $this->actingAs($worker)->get(route('invoice.vender'))->assertStatus(403);
        $this->actingAs($worker)->get(route('invoice.histry'))->assertStatus(403);
        $this->actingAs($worker)->get(route('listqutation'))->assertStatus(403);
    }

    public function test_worker_granted_a_single_permission_can_reach_only_that_route(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $worker->givePermissionTo('invoices.view');

        $this->actingAs($worker)->get(route('invoice'))->assertOk();
        $this->actingAs($worker)->get(route('product'))->assertStatus(403);
    }

    public function test_dashboard_is_reachable_by_worker_with_dashboard_permission(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $worker->givePermissionTo('dashboard.view');

        $this->actingAs($worker)->get(route('root'))->assertOk();
    }
}
