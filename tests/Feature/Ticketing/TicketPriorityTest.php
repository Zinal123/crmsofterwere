<?php
// tests/Feature/Ticketing/TicketPriorityTest.php
namespace Tests\Feature\Ticketing;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Ticket;
use App\Models\TicketProblemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_priority_defaults_from_the_problem_types_default_priority(): void
    {
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create(['default_priority' => 'urgent']);

        $response = $this->actingAs($account, 'client')->post(route('client.tickets.store'), [
            'client_machine_id' => $machine->id,
            'problem_type_id' => $problemType->id,
            'description' => 'Machine stopped completely',
        ]);

        $ticket = Ticket::where('client_machine_id', $machine->id)->first();
        $response->assertRedirect(route('client.tickets.show', $ticket->id));
        $this->assertSame('urgent', $ticket->priority);
    }

    public function test_ticket_priority_falls_back_to_medium_when_problem_type_has_no_explicit_priority(): void
    {
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create();

        $this->actingAs($account, 'client')->post(route('client.tickets.store'), [
            'client_machine_id' => $machine->id,
            'problem_type_id' => $problemType->id,
        ]);

        $ticket = Ticket::where('client_machine_id', $machine->id)->first();
        $this->assertSame('medium', $ticket->priority);
    }
}
