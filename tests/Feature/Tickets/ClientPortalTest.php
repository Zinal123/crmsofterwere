<?php

namespace Tests\Feature\Tickets;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketProblemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_log_in_with_email_and_password(): void
    {
        $account = ClientAccount::factory()->create(['email' => 'client@example.com', 'password' => Hash::make('password123')]);

        $response = $this->post(route('client.login.attempt'), [
            'login' => 'client@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticatedAs($account, 'client');
    }

    public function test_client_can_log_in_with_phone_and_password(): void
    {
        $account = ClientAccount::factory()->create(['phone' => '9876543210', 'password' => Hash::make('password123')]);

        $response = $this->post(route('client.login.attempt'), [
            'login' => '9876543210',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticatedAs($account, 'client');
    }

    public function test_wrong_password_is_rejected(): void
    {
        ClientAccount::factory()->create(['email' => 'client@example.com', 'password' => Hash::make('password123')]);

        $response = $this->postJson(route('client.login.attempt'), [
            'login' => 'client@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $this->assertGuest('client');
    }

    public function test_deactivated_client_cannot_log_in(): void
    {
        ClientAccount::factory()->create(['email' => 'client@example.com', 'password' => Hash::make('password123'), 'is_active' => false]);

        $response = $this->postJson(route('client.login.attempt'), [
            'login' => 'client@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $this->assertGuest('client');
    }

    public function test_staff_session_cannot_access_the_client_portal(): void
    {
        $response = $this->get(route('client.dashboard'));

        $response->assertRedirect(route('client.login'));
    }

    public function test_client_dashboard_shows_only_their_own_machines(): void
    {
        $account = ClientAccount::factory()->create();
        $otherAccount = ClientAccount::factory()->create();
        $myMachine = ClientMachine::factory()->create(['client_account_id' => $account->id, 'serial_number' => 'MY-MACHINE-001']);
        ClientMachine::factory()->create(['client_account_id' => $otherAccount->id, 'serial_number' => 'OTHER-MACHINE-002']);

        $response = $this->actingAs($account, 'client')->get(route('client.dashboard'));

        $response->assertOk();
        $response->assertSee('MY-MACHINE-001');
        $response->assertDontSee('OTHER-MACHINE-002');
    }

    public function test_client_can_raise_a_ticket_for_their_own_machine(): void
    {
        Storage::fake('public');
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create(['category' => 'mechanical']);

        $response = $this->actingAs($account, 'client')->post(route('client.tickets.store'), [
            'client_machine_id' => $machine->id,
            'problem_type_id' => $problemType->id,
            'description' => 'Nozzle keeps clogging',
        ]);

        $ticket = Ticket::where('client_machine_id', $machine->id)->first();
        $response->assertRedirect(route('client.tickets.show', $ticket->id));
        $this->assertSame($account->id, $ticket->client_account_id);
        $this->assertSame('open', $ticket->status);
    }

    public function test_client_cannot_raise_a_ticket_for_someone_elses_machine(): void
    {
        $account = ClientAccount::factory()->create();
        $otherAccount = ClientAccount::factory()->create();
        $otherMachine = ClientMachine::factory()->create(['client_account_id' => $otherAccount->id]);
        $problemType = TicketProblemType::factory()->create();

        $response = $this->actingAs($account, 'client')->post(route('client.tickets.store'), [
            'client_machine_id' => $otherMachine->id,
            'problem_type_id' => $problemType->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('tickets', ['client_machine_id' => $otherMachine->id]);
    }

    public function test_client_cannot_view_someone_elses_ticket(): void
    {
        $account = ClientAccount::factory()->create();
        $otherAccount = ClientAccount::factory()->create();
        $otherMachine = ClientMachine::factory()->create(['client_account_id' => $otherAccount->id]);
        $problemType = TicketProblemType::factory()->create();
        $otherTicket = Ticket::create([
            'client_machine_id' => $otherMachine->id,
            'client_account_id' => $otherAccount->id,
            'problem_type_id' => $problemType->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($account, 'client')->get(route('client.tickets.show', $otherTicket->id));

        $response->assertForbidden();
    }

    public function test_client_ticket_list_shows_only_their_own_tickets(): void
    {
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create(['name' => 'My Own Problem']);
        Ticket::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'problem_type_id' => $problemType->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($account, 'client')->get(route('client.tickets.index'));

        $response->assertOk();
        $response->assertSee('My Own Problem');
    }

    public function test_raising_a_ticket_with_a_photo_stores_it(): void
    {
        Storage::fake('public');
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create();

        $response = $this->actingAs($account, 'client')->post(route('client.tickets.store'), [
            'client_machine_id' => $machine->id,
            'problem_type_id' => $problemType->id,
            'photos' => [UploadedFile::fake()->image('fault.jpg')],
        ]);

        $ticket = Ticket::where('client_machine_id', $machine->id)->first();
        $response->assertRedirect(route('client.tickets.show', $ticket->id));
        $this->assertCount(1, $ticket->photos);
    }
}
