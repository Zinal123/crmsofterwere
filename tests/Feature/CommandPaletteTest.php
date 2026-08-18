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

    public function test_worker_kiosk_has_no_command_palette_but_keeps_a_profile_link(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        // root() sends Worker straight to the job kiosk (layouts.worker),
        // a deliberately stripped-down shop-floor surface that doesn't
        // include the admin-chrome command palette. It keeps a direct
        // "My Profile" link instead, so that access isn't lost entirely.
        $response = $this->actingAs($worker)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertDontSee('commandPalette');
        $response->assertSee('My Profile');
    }
}
