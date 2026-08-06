<?php

namespace Tests\Feature\Job;

use App\Models\Machine;
use App\Models\User;
use App\Notifications\MachineDownNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MachineDownFlagTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_worker_can_flag_a_machine_as_down(): void
    {
        Notification::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $machine = Machine::factory()->create(['name' => 'CNC Lathe #3']);

        $response = $this->actingAs($worker)->post(route('machines.flag-down', $machine->id), [
            'note' => 'Spindle making a grinding noise, stopped mid-cut.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('jobs', [
            'title' => 'Machine Down: CNC Lathe #3',
            'machine_id' => $machine->id,
            'priority' => 'urgent',
            'status' => 'pending_approval',
            'created_by' => $worker->id,
        ]);
        Notification::assertSentTo($owner, MachineDownNotification::class);
    }

    public function test_flagging_a_machine_down_works_without_a_note(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $machine = Machine::factory()->create();

        $response = $this->actingAs($worker)->post(route('machines.flag-down', $machine->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('jobs', ['machine_id' => $machine->id, 'priority' => 'urgent']);
    }

    public function test_flagging_a_nonexistent_machine_404s(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->post(route('machines.flag-down', 999999));

        $response->assertNotFound();
    }
}
