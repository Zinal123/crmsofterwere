<?php

namespace Tests\Feature\Tickets;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\JobPhoto;
use App\Models\Ticket;
use App\Models\TicketProblemType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPhotoAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_ticket_photo_upload_logs_a_created_entry(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create();
        $ticket = Ticket::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'problem_type_id' => $problemType->id,
            'description' => 'Beam misaligned',
            'status' => 'open',
        ]);

        $photo = JobPhoto::create([
            'ticket_id' => $ticket->id,
            'path' => 'ticket-photos/1/example.jpg',
        ]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'ticket_photo', 'id' => $photo->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_a_client_submitted_photo_does_not_break_the_audit_write(): void
    {
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create();
        $ticket = Ticket::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'problem_type_id' => $problemType->id,
            'description' => 'Beam misaligned',
            'status' => 'open',
        ]);

        // Simulates the client-guard request context (see EloquentAuditLogRepository -
        // must not try to attribute this to a `users` row and blow up the FK).
        $this->actingAs($account, 'client');
        $photo = JobPhoto::create(['ticket_id' => $ticket->id, 'path' => 'ticket-photos/1/example.jpg']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => 'ticket_photo',
            'auditable_id' => $photo->id,
            'action' => 'created',
            'user_id' => null,
        ]);
    }
}
