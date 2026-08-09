<?php

namespace Tests\Feature;

use App\Models\DailyTransaction;
use App\Models\ExpenseCategory;
use App\Models\Invetry;
use App\Models\Job;
use App\Models\Machine;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_owner_can_view_the_reports_page(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('reports.index'));

        $response->assertOk();
    }

    public function test_worker_cannot_view_the_reports_page(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('reports.index'));

        $response->assertForbidden();
    }

    public function test_job_throughput_counts_jobs_by_status(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        Job::factory()->create(['status' => 'completed']);
        Job::factory()->create(['status' => 'completed']);
        Job::factory()->create(['status' => 'in_progress']);
        Job::factory()->create(['status' => 'rejected']);

        $response = $this->actingAs($owner)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('jobThroughput', function ($throughput) {
            return $throughput['completed'] === 2
                && $throughput['in_progress'] === 1
                && $throughput['rejected'] === 1;
        });
    }

    public function test_technician_performance_shows_completed_count_per_worker(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $workerA = User::factory()->create(['name' => 'Ramesh Solanki']);
        $workerA->syncRoles(['Worker']);
        $workerB = User::factory()->create(['name' => 'Suresh Vaghela']);
        $workerB->syncRoles(['Worker']);

        Job::factory()->create(['assigned_to' => $workerA->id, 'status' => 'completed', 'created_at' => now()->subDays(2), 'completed_at' => now()->subDays(2)->addHours(3)]);
        Job::factory()->create(['assigned_to' => $workerA->id, 'status' => 'completed', 'created_at' => now()->subDays(1), 'completed_at' => now()->subDays(1)->addHours(5)]);
        Job::factory()->create(['assigned_to' => $workerB->id, 'status' => 'in_progress']);

        $response = $this->actingAs($owner)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Ramesh Solanki');
        $response->assertSee('Suresh Vaghela');
        // workerA has 2 completed jobs, workerB has 0
        $response->assertViewHas('technicianPerformance', function ($rows) use ($workerA, $workerB) {
            $a = $rows->firstWhere('id', $workerA->id);
            $b = $rows->firstWhere('id', $workerB->id);

            return $a['completed_count'] === 2 && $b['completed_count'] === 0;
        });
    }

    public function test_machine_service_history_counts_jobs_per_machine(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::factory()->create(['name' => 'CNC Lathe #9']);
        Job::factory()->create(['machine_id' => $machine->id, 'status' => 'completed']);
        Job::factory()->create(['machine_id' => $machine->id, 'status' => 'in_progress']);

        $response = $this->actingAs($owner)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('CNC Lathe #9');
        $response->assertViewHas('machineHistory', function ($rows) use ($machine) {
            $row = $rows->firstWhere('id', $machine->id);

            return $row['job_count'] === 2;
        });
    }

    public function test_inventory_levels_flags_low_stock(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $healthy = Product::factory()->create(['name' => 'Ceramic Nozzle Ring']);
        Invetry::factory()->create(['product_id' => $healthy->id, 'quantity' => 100]);
        $low = Product::factory()->create(['name' => 'Focus Lens']);
        Invetry::factory()->create(['product_id' => $low->id, 'quantity' => 2]);

        $response = $this->actingAs($owner)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Ceramic Nozzle Ring');
        $response->assertSee('Focus Lens');
        $response->assertSee('Low Stock');
    }

    public function test_fleet_status_marks_a_machine_with_an_overdue_job_as_overdue_above_all_else(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::factory()->create(['name' => 'Overdue Machine', 'is_active' => true]);
        Job::factory()->create(['machine_id' => $machine->id, 'status' => 'in_progress', 'overdue_flagged_at' => now()]);

        $response = $this->actingAs($owner)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('fleetStatus', function ($rows) use ($machine) {
            return $rows->firstWhere('id', $machine->id)['status'] === 'overdue';
        });
    }

    public function test_fleet_status_marks_an_inactive_machine_as_not_reporting(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::factory()->create(['name' => 'Retired Machine', 'is_active' => false]);

        $response = $this->actingAs($owner)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Retired Machine');
        $response->assertViewHas('fleetStatus', function ($rows) use ($machine) {
            return $rows->firstWhere('id', $machine->id)['status'] === 'inactive';
        });
    }

    public function test_fleet_status_marks_a_machine_with_an_in_progress_job_as_active(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::factory()->create(['is_active' => true]);
        Job::factory()->create(['machine_id' => $machine->id, 'status' => 'in_progress']);

        $response = $this->actingAs($owner)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('fleetStatus', function ($rows) use ($machine) {
            return $rows->firstWhere('id', $machine->id)['status'] === 'active';
        });
    }

    public function test_fleet_status_marks_a_machine_with_a_pending_job_as_setup(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::factory()->create(['is_active' => true]);
        Job::factory()->create(['machine_id' => $machine->id, 'status' => 'assigned']);

        $response = $this->actingAs($owner)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('fleetStatus', function ($rows) use ($machine) {
            return $rows->firstWhere('id', $machine->id)['status'] === 'setup';
        });
    }

    public function test_fleet_status_marks_a_machine_with_no_open_jobs_as_idle(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::factory()->create(['is_active' => true]);
        Job::factory()->create(['machine_id' => $machine->id, 'status' => 'completed']);

        $response = $this->actingAs($owner)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('fleetStatus', function ($rows) use ($machine) {
            return $rows->firstWhere('id', $machine->id)['status'] === 'idle';
        });
    }

    public function test_reports_page_shows_expenses_grouped_by_category(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $diesel = ExpenseCategory::create(['name' => 'Diesel / Fuel', 'type' => 'payment', 'party_model' => null]);
        $electricity = ExpenseCategory::create(['name' => 'Electricity', 'type' => 'payment', 'party_model' => null]);
        DailyTransaction::create(['type' => 'payment', 'expense_category_id' => $diesel->id, 'amount' => 2000, 'date' => now(), 'payment_mode' => 'cash', 'created_by' => $owner->id]);
        DailyTransaction::create(['type' => 'payment', 'expense_category_id' => $diesel->id, 'amount' => 1500, 'date' => now(), 'payment_mode' => 'cash', 'created_by' => $owner->id]);
        DailyTransaction::create(['type' => 'payment', 'expense_category_id' => $electricity->id, 'amount' => 3200, 'date' => now(), 'payment_mode' => 'bank', 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Diesel / Fuel');
        $response->assertViewHas('expensesByCategory', function ($rows) {
            $diesel = $rows->firstWhere('category', 'Diesel / Fuel');

            return $diesel['total'] === 3500.0;
        });
    }
}
