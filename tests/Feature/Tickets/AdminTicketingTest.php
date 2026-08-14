<?php

namespace Tests\Feature\Tickets;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketProblemType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTicketingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_owner_can_create_a_client_account(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('admin.client-accounts.store'), [
            'name' => 'Ramesh Solanki',
            'email' => 'ramesh@example.com',
            'phone' => '9876543210',
            'password' => 'TempPass123',
        ]);

        $response->assertRedirect(route('admin.client-accounts.index'));
        $account = ClientAccount::where('email', 'ramesh@example.com')->first();
        $this->assertNotNull($account);
        $this->assertTrue(Hash::check('TempPass123', $account->password));
    }

    public function test_owner_can_register_a_client_machine(): void
    {
        $owner = User::factory()->create();
        $account = ClientAccount::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($owner)->post(route('admin.client-machines.store'), [
            'client_account_id' => $account->id,
            'product_id' => $product->id,
            'serial_number' => 'OMT-2026-001',
            'installed_at' => '2026-01-15',
        ]);

        $response->assertRedirect(route('admin.client-machines.index'));
        $this->assertDatabaseHas('client_machines', [
            'client_account_id' => $account->id,
            'product_id' => $product->id,
            'serial_number' => 'OMT-2026-001',
        ]);
    }

    public function test_owner_can_add_and_toggle_a_problem_type(): void
    {
        $owner = User::factory()->create();

        $storeResponse = $this->actingAs($owner)->post(route('admin.ticket-problem-types.store'), [
            'category' => 'electrical',
            'name' => 'Custom Fault',
            'default_priority' => 'medium',
        ]);
        $storeResponse->assertRedirect(route('admin.ticket-problem-types.index'));

        $problemType = TicketProblemType::where('name', 'Custom Fault')->first();
        $this->assertTrue($problemType->is_active);

        $toggleResponse = $this->actingAs($owner)->post(route('admin.ticket-problem-types.toggle', $problemType->id));
        $toggleResponse->assertRedirect(route('admin.ticket-problem-types.index'));
        $this->assertFalse($problemType->fresh()->is_active);
    }

    public function test_ticket_inbox_lists_tickets_with_details(): void
    {
        $owner = User::factory()->create();
        $account = ClientAccount::factory()->create(['name' => 'Solanki Fabricators']);
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create(['name' => 'Power Supply Failure']);
        Ticket::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'problem_type_id' => $problemType->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($owner)->get(route('admin.tickets.index'));

        $response->assertOk();
        $response->assertSee('Solanki Fabricators');
        $response->assertSee('Power Supply Failure');
    }

    public function test_owner_can_assign_a_ticket_which_creates_a_real_job(): void
    {
        $owner = User::factory()->create();
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create();
        $ticket = Ticket::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'problem_type_id' => $problemType->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($owner)->post(route('admin.tickets.assign', $ticket->id), [
            'worker_id' => $worker->id,
        ]);

        $response->assertRedirect(route('admin.tickets.show', $ticket->id));
        $ticket->refresh();
        $this->assertSame('assigned', $ticket->status);
        $this->assertNotNull($ticket->job_id);
        $this->assertDatabaseHas('jobs', [
            'id' => $ticket->job_id,
            'assigned_to' => $worker->id,
            'status' => 'assigned',
        ]);
    }

    public function test_assigning_an_already_assigned_ticket_is_rejected(): void
    {
        $owner = User::factory()->create();
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create();
        $ticket = Ticket::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'problem_type_id' => $problemType->id,
            'status' => 'assigned',
        ]);

        $response = $this->actingAs($owner)->post(route('admin.tickets.assign', $ticket->id), [
            'worker_id' => $worker->id,
        ]);

        $response->assertRedirect(route('admin.tickets.show', $ticket->id));
        $response->assertSessionHas('error');
    }

    public function test_worker_cannot_reach_the_ticket_inbox(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('admin.tickets.index'));

        $response->assertStatus(403);
    }
}
