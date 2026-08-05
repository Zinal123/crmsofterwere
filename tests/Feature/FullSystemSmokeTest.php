<?php

namespace Tests\Feature;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Invetry;
use App\Models\Job;
use App\Models\JobPhoto;
use App\Models\Machine;
use App\Models\Product;
use App\Models\SparePartRequest;
use App\Models\Ticket;
use App\Models\TicketProblemType;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * End-to-end functional pass across all three real user types (Owner, Worker,
 * Client), against a realistic multi-entity dataset, checking actual rendered
 * field content (assertSee), not just HTTP status codes. This is a black-box
 * "does the real workflow actually work" pass, layered on top of the existing
 * per-feature unit/feature tests rather than replacing them.
 */
class FullSystemSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $workerA;
    private User $workerB;
    private ClientAccount $clientAlpha;
    private ClientAccount $clientBeta;
    private ClientMachine $alphaMachine;
    private ClientMachine $betaMachine;
    private Machine $internalMachine;
    private Product $nozzle;
    private Product $lens;
    private Job $overdueJob;
    private Job $completedJob;
    private Job $pendingApprovalJob;
    private SparePartRequest $availableRequest;
    private SparePartRequest $unavailableRequest;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->owner = User::factory()->create(['name' => 'Meet Patel']);
        $this->owner->assignRole('Owner');

        $this->workerA = User::factory()->create(['name' => 'Ramesh Solanki']);
        $this->workerA->syncRoles(['Worker']);
        $this->workerB = User::factory()->create(['name' => 'Suresh Vaghela']);
        $this->workerB->syncRoles(['Worker']);

        $this->clientAlpha = ClientAccount::factory()->create(['name' => 'Solanki Fabricators']);
        $this->clientBeta = ClientAccount::factory()->create(['name' => 'Patel CNC Works']);

        $fiberMachine = Product::factory()->create(['name' => 'Fiber Laser Cutting Machine 1500W']);
        $this->alphaMachine = ClientMachine::factory()->create([
            'client_account_id' => $this->clientAlpha->id,
            'product_id' => $fiberMachine->id,
            'serial_number' => 'OMT-2024-A101',
        ]);
        $this->betaMachine = ClientMachine::factory()->create([
            'client_account_id' => $this->clientBeta->id,
            'product_id' => $fiberMachine->id,
            'serial_number' => 'OMT-2024-B202',
        ]);

        $this->internalMachine = Machine::factory()->create(['name' => 'CNC Lathe #3', 'is_active' => true, 'latitude' => 21.1702, 'longitude' => 72.8311]);

        $this->nozzle = Product::factory()->create(['name' => 'Ceramic Nozzle Ring', 'is_spare_part' => true]);
        Invetry::factory()->create(['product_id' => $this->nozzle->id, 'quantity' => 50]);

        $this->lens = Product::factory()->create(['name' => 'Precitec Focusing Lens', 'is_spare_part' => true]);
        Invetry::factory()->create(['product_id' => $this->lens->id, 'quantity' => 2]);

        $this->overdueJob = Job::factory()->create([
            'title' => 'Replace fiber laser nozzle',
            'machine_id' => $this->internalMachine->id,
            'status' => 'in_progress',
            'assigned_to' => $this->workerA->id,
            'created_by' => $this->owner->id,
            'due_date' => now()->subDays(3),
        ]);

        $this->completedJob = Job::factory()->create([
            'title' => 'Annual calibration service',
            'machine_id' => $this->internalMachine->id,
            'status' => 'completed',
            'assigned_to' => $this->workerA->id,
            'created_by' => $this->owner->id,
            'completion_notes' => 'Calibrated cutting head, verified beam alignment within spec.',
            'completed_at' => now()->subDay(),
        ]);
        JobPhoto::create([
            'job_id' => $this->completedJob->id,
            'uploaded_by' => $this->workerA->id,
            'path' => 'job-photos/calibration-proof.jpg',
            'captured_at' => now()->subDay(),
            'location_captured' => true,
            'address' => 'Oracle Machine Tech, Surat',
        ]);

        $this->pendingApprovalJob = Job::factory()->create([
            'title' => 'Inspect CO2 tube leak',
            'status' => 'pending_approval',
            'assigned_to' => $this->workerB->id,
            'created_by' => $this->workerB->id,
        ]);

        $problemType = TicketProblemType::factory()->create(['name' => 'Beam misalignment']);
        Ticket::create([
            'client_machine_id' => $this->alphaMachine->id,
            'client_account_id' => $this->clientAlpha->id,
            'problem_type_id' => $problemType->id,
            'description' => 'Cutting quality dropped, suspect beam misalignment.',
            'status' => 'open',
        ]);

        $this->availableRequest = SparePartRequest::create([
            'client_machine_id' => $this->alphaMachine->id,
            'client_account_id' => $this->clientAlpha->id,
            'product_id' => $this->nozzle->id,
            'quantity' => 5,
            'status' => 'pending',
            'note' => 'Nozzle worn out, need replacement urgently.',
        ]);
        $this->unavailableRequest = SparePartRequest::create([
            'client_machine_id' => $this->betaMachine->id,
            'client_account_id' => $this->clientBeta->id,
            'product_id' => $this->lens->id,
            'quantity' => 10,
            'status' => 'pending',
            'note' => 'Need 10 lenses for a bulk service job.',
        ]);

        Vendor::create([
            'name' => 'Raytools India Pvt Ltd',
            'category' => 'Accessories',
            'contact_name' => 'Vikram Shah',
            'phone' => '9825012345',
            'email' => 'vikram@raytools.example.com',
            'state' => 'Gujarat',
            'country' => 'India',
            'is_active' => true,
        ]);

        $this->artisan('jobs:flag-overdue');
    }

    // ---------------------------------------------------------------------
    // Owner journey
    // ---------------------------------------------------------------------

    public function test_owner_dashboard_loads_with_real_data(): void
    {
        $response = $this->actingAs($this->owner)->get(route('root'));

        $response->assertOk();
    }

    public function test_owner_sees_all_jobs_with_correct_overdue_badge(): void
    {
        $response = $this->actingAs($this->owner)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertSee('Replace fiber laser nozzle');
        $response->assertSee('Annual calibration service');
        $response->assertSee('Inspect CO2 tube leak');
        $response->assertSee('Overdue');
    }

    public function test_owner_can_view_job_detail_and_approve_a_pending_job(): void
    {
        $showResponse = $this->actingAs($this->owner)->get(route('jobs.show', $this->pendingApprovalJob->id));
        $showResponse->assertOk();
        $showResponse->assertSee('Inspect CO2 tube leak');

        $approveResponse = $this->actingAs($this->owner)->post(route('jobs.approve', $this->pendingApprovalJob->id), [
            'assigned_to' => $this->workerB->id,
        ]);

        $approveResponse->assertRedirect();
        $this->assertSame('assigned', $this->pendingApprovalJob->fresh()->status);
    }

    public function test_owner_can_download_completed_job_report_with_real_photo_data(): void
    {
        $response = $this->actingAs($this->owner)->get(route('jobs.pdf', $this->completedJob->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_owner_sees_available_and_not_available_badges_on_real_spare_part_requests(): void
    {
        $response = $this->actingAs($this->owner)->get(route('admin.spare-part-requests.index'));

        $response->assertOk();
        $response->assertSee('Solanki Fabricators');
        $response->assertSee('Patel CNC Works');
        $response->assertSee('Ceramic Nozzle Ring');
        $response->assertSee('Precitec Focusing Lens');
        $response->assertSee('Available');
        $response->assertSee('Not Available');
    }

    public function test_owner_can_fulfill_a_request_with_sufficient_stock(): void
    {
        $response = $this->actingAs($this->owner)->post(
            route('admin.spare-part-requests.update-status', $this->availableRequest->id),
            ['status' => 'fulfilled']
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame('fulfilled', $this->availableRequest->fresh()->status);
        $this->assertSame(45, Invetry::where('product_id', $this->nozzle->id)->value('quantity'));
    }

    public function test_owner_is_blocked_from_fulfilling_a_request_with_insufficient_stock(): void
    {
        $response = $this->actingAs($this->owner)->post(
            route('admin.spare-part-requests.update-status', $this->unavailableRequest->id),
            ['status' => 'fulfilled']
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame('pending', $this->unavailableRequest->fresh()->status);
        $this->assertSame(2, Invetry::where('product_id', $this->lens->id)->value('quantity'));
    }

    public function test_owner_can_view_machines_vendors_tickets_and_workforce_screens(): void
    {
        $this->actingAs($this->owner)->get(route('machines.index'))->assertOk()->assertSee('CNC Lathe #3');
        $this->actingAs($this->owner)->get(route('admin.vendors.index'))->assertOk()->assertSee('Raytools India Pvt Ltd');
        $this->actingAs($this->owner)->get(route('admin.tickets.index'))->assertOk()->assertSee('Beam misalignment');
        $this->actingAs($this->owner)->get(route('employees.create'))->assertOk();
        $this->actingAs($this->owner)->get(route('admin.roles.index'))->assertOk()->assertSee('Owner')->assertSee('Worker');
    }

    // ---------------------------------------------------------------------
    // Worker journey
    // ---------------------------------------------------------------------

    public function test_worker_sees_only_their_own_jobs(): void
    {
        $response = $this->actingAs($this->workerA)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertSee('Replace fiber laser nozzle');
        $response->assertSee('Annual calibration service');
        $response->assertDontSee('Inspect CO2 tube leak');
    }

    public function test_worker_cannot_open_another_workers_job(): void
    {
        $response = $this->actingAs($this->workerA)->get(route('jobs.show', $this->pendingApprovalJob->id));

        $response->assertForbidden();
    }

    public function test_worker_can_start_upload_photo_and_complete_an_assigned_job(): void
    {
        $job = Job::factory()->create([
            'title' => 'Replace damaged rack and pinion',
            'status' => 'assigned',
            'assigned_to' => $this->workerA->id,
            'created_by' => $this->owner->id,
        ]);

        $this->actingAs($this->workerA)->post(route('jobs.start', $job->id))->assertRedirect();
        $this->assertSame('in_progress', $job->fresh()->status);

        $photoResponse = $this->actingAs($this->workerA)->post(route('jobs.photos.store', $job->id), [
            'photo' => UploadedFile::fake()->image('rack-replaced.jpg'),
            'latitude' => 21.17,
            'longitude' => 72.83,
        ]);
        $photoResponse->assertRedirect();
        $this->assertSame(1, $job->photos()->count());

        $completeResponse = $this->actingAs($this->workerA)->post(route('jobs.complete', $job->id), [
            'completion_notes' => 'Rack and pinion replaced, machine test-run OK.',
        ]);
        $completeResponse->assertRedirect();
        $this->assertSame('completed', $job->fresh()->status);
    }

    public function test_worker_cannot_reach_owner_only_screens(): void
    {
        $this->actingAs($this->workerA)->get(route('machines.index'))->assertForbidden();
        $this->actingAs($this->workerA)->get(route('admin.spare-part-requests.index'))->assertForbidden();
        $this->actingAs($this->workerA)->get(route('admin.roles.index'))->assertForbidden();
        $this->actingAs($this->workerA)->get(route('admin.vendors.index'))->assertForbidden();
    }

    // ---------------------------------------------------------------------
    // Client journey
    // ---------------------------------------------------------------------

    public function test_client_sees_only_their_own_spare_part_requests(): void
    {
        $response = $this->actingAs($this->clientAlpha, 'client')->get(route('client.spare-parts.index'));

        $response->assertOk();
        $response->assertSee('Ceramic Nozzle Ring');
        $response->assertDontSee('Precitec Focusing Lens');
    }

    public function test_client_can_submit_a_ticket_for_their_own_machine(): void
    {
        $problemType = TicketProblemType::factory()->create(['name' => 'Motor overheating']);

        $response = $this->actingAs($this->clientAlpha, 'client')->post(route('client.tickets.store'), [
            'client_machine_id' => $this->alphaMachine->id,
            'problem_type_id' => $problemType->id,
            'description' => 'Servo motor getting very hot after 20 minutes of cutting.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'client_machine_id' => $this->alphaMachine->id,
            'description' => 'Servo motor getting very hot after 20 minutes of cutting.',
        ]);
    }

    public function test_client_cannot_view_another_clients_machine_spare_part_form(): void
    {
        $response = $this->actingAs($this->clientAlpha, 'client')->get(route('client.spare-parts.create', $this->betaMachine->id));

        $response->assertForbidden();
    }

    public function test_client_sees_real_available_quantity_when_requesting_a_part(): void
    {
        $response = $this->actingAs($this->clientAlpha, 'client')->get(route('client.spare-parts.create', $this->alphaMachine->id));

        $response->assertOk();
        $response->assertSee('Ceramic Nozzle Ring');
        // 50 on hand, 5 already reserved by $this->availableRequest.
        $response->assertSee('Available: 45');
    }
}
