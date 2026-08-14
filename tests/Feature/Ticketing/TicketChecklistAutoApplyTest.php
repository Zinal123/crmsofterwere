<?php
// tests/Feature/Ticketing/TicketChecklistAutoApplyTest.php
namespace Tests\Feature\Ticketing;

use App\Models\ChecklistTemplate;
use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Ticket;
use App\Models\TicketProblemType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketChecklistAutoApplyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_assigning_a_ticket_applies_its_problem_types_active_checklist_template_to_the_job(): void
    {
        $owner = User::factory()->create();
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $template = ChecklistTemplate::create(['name' => 'Hydraulic PM', 'is_active' => true]);
        $template->items()->create(['description' => 'Check hydraulic fluid level', 'position' => 1]);
        $template->items()->create(['description' => 'Inspect hoses for leaks', 'position' => 2]);

        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create(['checklist_template_id' => $template->id]);
        $ticket = Ticket::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'problem_type_id' => $problemType->id,
            'status' => 'open',
        ]);

        $this->actingAs($owner)->post(route('admin.tickets.assign', $ticket->id), ['worker_id' => $worker->id]);

        $job = $ticket->fresh()->job;

        $this->assertCount(2, $job->checklistItems);
        $this->assertEqualsCanonicalizing(
            ['Check hydraulic fluid level', 'Inspect hoses for leaks'],
            $job->checklistItems->pluck('description')->all()
        );
    }
}
