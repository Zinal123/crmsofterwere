<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTableSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_users_page_loads_datatables_js(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('DataTable(', false);
    }

    public function test_roles_page_loads_datatables_js(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertSee('DataTable(', false);
    }

    public function test_users_page_still_shows_all_users_and_create_form(): void
    {
        $owner = User::factory()->create(['name' => 'Table Test Owner']);

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Table Test Owner');
        $response->assertSee('Create User');
    }
}
