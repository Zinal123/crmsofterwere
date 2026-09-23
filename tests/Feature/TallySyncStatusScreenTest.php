<?php

namespace Tests\Feature;

use App\Models\TallySyncQueue;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallySyncStatusScreenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function owner(): User
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        return $owner;
    }

    public function test_owner_can_view_the_status_screen(): void
    {
        $owner = $this->owner();

        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'SALES-1', 'payload' => [], 'status' => 'failed', 'last_error' => 'Ledger not found']);

        $response = $this->actingAs($owner)->get(route('tally-sync.index'));

        $response->assertOk();
        $response->assertSee('SALES-1');
        $response->assertSee('Ledger not found');
    }

    public function test_worker_cannot_view_the_status_screen(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $this->actingAs($worker)->get(route('tally-sync.index'))->assertForbidden();
    }

    public function test_owner_can_retry_a_failed_row(): void
    {
        $owner = $this->owner();
        $queue = TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'SALES-1', 'payload' => [], 'status' => 'failed', 'last_error' => 'Ledger not found', 'attempts' => 2]);

        $this->actingAs($owner)->post(route('tally-sync.retry', $queue))->assertRedirect(route('tally-sync.index'));

        $this->assertDatabaseHas('tally_sync_queue', ['id' => $queue->id, 'status' => 'pending', 'last_error' => null]);
    }

    public function test_status_screen_warns_when_the_agent_has_never_checked_in(): void
    {
        $owner = $this->owner();
        \Illuminate\Support\Facades\Cache::forget('tally_agent_last_checkin');

        $this->actingAs($owner)->get(route('tally-sync.index'))->assertSee('Agent has not checked in');
    }

    public function test_status_screen_does_not_warn_when_the_agent_checked_in_recently(): void
    {
        $owner = $this->owner();
        \Illuminate\Support\Facades\Cache::put('tally_agent_last_checkin', now(), now()->addDay());

        $this->actingAs($owner)->get(route('tally-sync.index'))->assertDontSee('Agent has not checked in');
    }
}
