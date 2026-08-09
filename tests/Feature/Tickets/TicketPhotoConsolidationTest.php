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
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TicketPhotoConsolidationTest extends TestCase
{
    use RefreshDatabase;

    private function ticket(): Ticket
    {
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create();

        return Ticket::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'problem_type_id' => $problemType->id,
            'description' => 'Beam misaligned',
            'status' => 'open',
        ]);
    }

    public function test_ticket_photos_table_no_longer_exists(): void
    {
        $this->assertFalse(Schema::hasTable('ticket_photos'), 'ticket_photos should be merged into job_photos.');
        $this->assertTrue(Schema::hasColumn('job_photos', 'ticket_id'));
    }

    public function test_a_ticket_photo_is_a_job_photo_row_with_a_null_job_id(): void
    {
        $ticket = $this->ticket();

        $photo = $ticket->photos()->create(['path' => 'ticket-photos/1/example.jpg']);

        $this->assertInstanceOf(JobPhoto::class, $photo);
        $this->assertNull($photo->job_id);
        $this->assertSame($ticket->id, $photo->ticket_id);
        $this->assertDatabaseHas('job_photos', ['id' => $photo->id, 'ticket_id' => $ticket->id, 'job_id' => null]);
    }

    public function test_a_ticket_photo_upload_still_logs_under_the_ticket_photo_audit_type(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $ticket = $this->ticket();

        $photo = $ticket->photos()->create(['path' => 'ticket-photos/1/example.jpg']);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'ticket_photo', 'id' => $photo->id]));

        $response->assertOk();
        $response->assertSee('created');
    }
}
