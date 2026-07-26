<?php

namespace Tests\Feature\Tickets;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Ticket;
use App\Models\TicketProblemType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketJobSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    private function assignedTicket(): array
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

        $this->actingAs($owner)->post(route('admin.tickets.assign', $ticket->id), ['worker_id' => $worker->id]);

        return [$ticket->fresh(), $worker];
    }

    public function test_starting_the_linked_job_moves_the_ticket_to_in_progress(): void
    {
        [$ticket, $worker] = $this->assignedTicket();

        $this->actingAs($worker)->post(route('jobs.start', $ticket->job_id));

        $this->assertSame('in_progress', $ticket->fresh()->status);
    }

    public function test_completing_the_linked_job_resolves_the_ticket(): void
    {
        Storage::fake('public');
        [$ticket, $worker] = $this->assignedTicket();

        $this->actingAs($worker)->post(route('jobs.start', $ticket->job_id));
        $this->actingAs($worker)->post(route('jobs.photos.store', $ticket->job_id), [
            'photo' => UploadedFile::fake()->image('proof.jpg'),
        ]);
        $this->actingAs($worker)->post(route('jobs.complete', $ticket->job_id), [
            'completion_notes' => 'Replaced the nozzle.',
        ]);

        $this->assertSame('resolved', $ticket->fresh()->status);
    }

    public function test_holding_the_linked_job_keeps_the_ticket_in_progress(): void
    {
        [$ticket, $worker] = $this->assignedTicket();

        $this->actingAs($worker)->post(route('jobs.start', $ticket->job_id));
        $this->actingAs($worker)->post(route('jobs.hold', $ticket->job_id), ['on_hold_reason' => 'Waiting for a spare part']);

        $this->assertSame('in_progress', $ticket->fresh()->status);
    }
}
