<?php

namespace Tests\Feature\Job;

use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineQrScanningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_machines_index_renders_a_qr_code_button_per_machine_with_its_token(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::factory()->create(['name' => 'CNC Lathe #3']);

        $response = $this->actingAs($owner)->get(route('machines.index'));

        $response->assertOk();
        $response->assertSee('data-machine-token="OMT-MACHINE-' . $machine->id . '"', false);
        $response->assertSee('QRCode', false);
    }

    public function test_job_create_page_renders_the_qr_scan_button_and_decoder(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('jobs.create'));

        $response->assertOk();
        $response->assertSee('id="scan-machine-qr-btn"', false);
        $response->assertSee('jsQR', false);
        $response->assertSee('OMT-MACHINE-', false);
        // Dropdown remains as the fallback for untagged machines.
        $response->assertSee('id="job-machine"', false);
    }
}
