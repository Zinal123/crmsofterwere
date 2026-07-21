<?php

namespace Tests\Feature\Job;

use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_toggling_a_machine_off_logs_deactivated(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::create(['name' => 'Fiber Laser #1', 'is_active' => true]);

        $this->actingAs($owner)->post(route('machines.toggle', $machine->id));

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'machine', 'id' => $machine->id]));

        $response->assertOk();
        $response->assertSee('deactivated');
    }

    public function test_machines_list_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::create(['name' => 'CO2 Laser #1', 'is_active' => true]);

        $response = $this->actingAs($owner)->get(route('machines.index'));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $machine->id . '"', false);
    }
}
