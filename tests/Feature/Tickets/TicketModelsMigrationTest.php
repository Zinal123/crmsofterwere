<?php

namespace Tests\Feature\Tickets;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Job;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\TicketPhoto;
use App\Models\TicketProblemType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketModelsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_ticket_tables_migrate_and_relate_correctly(): void
    {
        $account = ClientAccount::factory()->create();
        $product = Product::factory()->create();
        $machine = ClientMachine::factory()->create([
            'client_account_id' => $account->id,
            'product_id' => $product->id,
        ]);
        $problemType = TicketProblemType::factory()->create(['category' => 'electrical']);

        $ticket = Ticket::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'problem_type_id' => $problemType->id,
            'description' => 'Machine not powering on',
            'status' => 'open',
        ]);

        $photo = TicketPhoto::create(['ticket_id' => $ticket->id, 'path' => 'ticket-photos/test.jpg']);

        $this->assertTrue($account->machines->first()->is($machine));
        $this->assertTrue($account->tickets->first()->is($ticket));
        $this->assertTrue($machine->tickets->first()->is($ticket));
        $this->assertTrue($ticket->clientMachine->is($machine));
        $this->assertTrue($ticket->problemType->is($problemType));
        $this->assertTrue($ticket->photos->first()->is($photo));
        $this->assertSame($product->id, $machine->product->id);
    }

    public function test_ticket_links_to_a_real_job_and_back(): void
    {
        $user = User::factory()->create();
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $problemType = TicketProblemType::factory()->create();

        $job = Job::create([
            'title' => 'Fix electrical fault',
            'site_name' => 'Client site',
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'status' => 'assigned',
        ]);

        $ticket = Ticket::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'problem_type_id' => $problemType->id,
            'status' => 'assigned',
            'job_id' => $job->id,
        ]);

        $this->assertTrue($ticket->job->is($job));
        $this->assertTrue($job->ticket->is($ticket));
    }
}
