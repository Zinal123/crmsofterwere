<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ChartOfAccountsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ChartOfAccountsSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->syncRoles([$role]);

        return $user;
    }

    public function test_owner_lands_on_the_strategic_dashboard(): void
    {
        $response = $this->actingAs($this->userWithRole('Owner'))->get(route('root'));

        $response->assertOk();
        $response->assertSee('Total Revenue');
        $response->assertSee('This Month'); // financial snapshot
    }

    public function test_manager_lands_on_the_operational_dashboard(): void
    {
        $response = $this->actingAs($this->userWithRole('Manager'))->get(route('root'));

        $response->assertOk();
        $response->assertSee('Open Tickets');
        $response->assertSee('Completions by Worker (Today)');
    }

    public function test_account_lands_on_the_finance_dashboard(): void
    {
        $response = $this->actingAs($this->userWithRole('Account'))->get(route('root'));

        $response->assertOk();
        $response->assertSee('Receivables');
        $response->assertSee('Payables');
    }

    public function test_worker_lands_on_the_my_day_dashboard(): void
    {
        $response = $this->actingAs($this->userWithRole('Worker'))->get(route('root'));

        $response->assertOk();
        $response->assertSee('My Active Jobs');
    }

    public function test_each_scaffolded_role_now_has_dashboard_access(): void
    {
        foreach (['Owner', 'Manager', 'Account', 'Worker'] as $role) {
            $this->assertTrue(
                $this->userWithRole($role)->can('dashboard.view'),
                "$role should be able to view the dashboard"
            );
        }
    }
}
