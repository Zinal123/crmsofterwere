<?php

namespace Tests\Feature\Tickets;

use App\Models\ClientAccount;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAccountEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_owner_can_update_a_client_accounts_details(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $account = ClientAccount::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

        $response = $this->actingAs($owner)->put(route('admin.client-accounts.update', $account->id), [
            'name' => 'Solanki Fabricators Pvt Ltd',
            'email' => 'new@example.com',
            'phone' => '9998887777',
        ]);

        $response->assertRedirect(route('admin.client-accounts.index'));
        $this->assertDatabaseHas('client_accounts', ['id' => $account->id, 'name' => 'Solanki Fabricators Pvt Ltd', 'email' => 'new@example.com']);
    }

    public function test_owner_can_deactivate_and_reactivate_a_client_account(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $account = ClientAccount::factory()->create(['is_active' => true]);

        $this->actingAs($owner)->post(route('admin.client-accounts.toggle', $account->id));
        $this->assertFalse($account->fresh()->is_active);

        $this->actingAs($owner)->post(route('admin.client-accounts.toggle', $account->id));
        $this->assertTrue($account->fresh()->is_active);
    }

    public function test_client_accounts_page_has_edit_and_deactivate_triggers_per_row(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $account = ClientAccount::factory()->create();

        $response = $this->actingAs($owner)->get(route('admin.client-accounts.index'));

        $response->assertOk();
        $response->assertSee('data-bs-target="#editClientAccount-' . $account->id . '"', false);
        $response->assertSee(route('admin.client-accounts.toggle', $account->id), false);
    }

    public function test_client_accounts_page_has_a_history_trigger_per_row(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $account = ClientAccount::factory()->create();

        $response = $this->actingAs($owner)->get(route('admin.client-accounts.index'));

        $response->assertOk();
        $response->assertSee('data-bs-target="#auditTrailModal-client_account"', false);
        $response->assertSee('data-audit-id="' . $account->id . '"', false);
    }

    public function test_client_account_history_is_viewable_and_shows_the_creation_entry(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $account = ClientAccount::factory()->create();

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'client_account', 'id' => $account->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_worker_cannot_update_a_client_account(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $account = ClientAccount::factory()->create();

        $response = $this->actingAs($worker)->put(route('admin.client-accounts.update', $account->id), ['name' => 'Blocked Update']);

        $response->assertForbidden();
    }
}
