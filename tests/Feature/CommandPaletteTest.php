<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ChartOfAccountsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandPaletteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ChartOfAccountsSeeder::class);
    }

    public function test_owner_sees_the_palette_with_admin_commands(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('root'));

        $response->assertOk();
        $response->assertSee('commandPalette');
        $response->assertSee('Search pages and actions', false);
        $response->assertSee('Chart of Accounts');
        $response->assertSee('Users');
        $response->assertSee('My Profile');
    }

    public function test_worker_only_sees_commands_they_can_access(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('root'));

        $response->assertOk();
        $response->assertSee('commandPalette');
        // Allowed for a worker:
        $response->assertSee('Jobs');
        $response->assertSee('My Profile');
        // Gated away from a worker:
        $response->assertDontSee('Chart of Accounts');
        $response->assertDontSee('Roles &amp; Permissions');
        $response->assertDontSee('Create Invoice');
    }
}
