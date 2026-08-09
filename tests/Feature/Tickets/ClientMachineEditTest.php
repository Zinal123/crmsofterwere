<?php

namespace Tests\Feature\Tickets;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientMachineEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_owner_can_update_a_client_machine(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $account = ClientAccount::factory()->create();
        $product = Product::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id, 'product_id' => $product->id, 'serial_number' => 'OLD-SN-1']);

        $newAccount = ClientAccount::factory()->create();
        $newProduct = Product::factory()->create();

        $response = $this->actingAs($owner)->put(route('admin.client-machines.update', $machine->id), [
            'client_account_id' => $newAccount->id,
            'product_id' => $newProduct->id,
            'serial_number' => 'NEW-SN-2',
            'installed_at' => '2026-01-15',
        ]);

        $response->assertRedirect(route('admin.client-machines.index'));
        $this->assertDatabaseHas('client_machines', [
            'id' => $machine->id,
            'client_account_id' => $newAccount->id,
            'product_id' => $newProduct->id,
            'serial_number' => 'NEW-SN-2',
        ]);
    }

    public function test_client_machines_page_has_an_edit_trigger_per_row(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = ClientMachine::factory()->create();

        $response = $this->actingAs($owner)->get(route('admin.client-machines.index'));

        $response->assertOk();
        $response->assertSee('data-bs-target="#editClientMachine-' . $machine->id . '"', false);
    }

    public function test_worker_cannot_update_a_client_machine(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $machine = ClientMachine::factory()->create();

        $response = $this->actingAs($worker)->put(route('admin.client-machines.update', $machine->id), ['serial_number' => 'BLOCKED']);

        $response->assertForbidden();
    }
}
