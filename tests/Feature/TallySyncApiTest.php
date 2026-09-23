<?php

namespace Tests\Feature;

use App\Models\TallySyncQueue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TallySyncApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_token_can_list_pending_rows_only(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['tally-agent']);

        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'A', 'payload' => [], 'status' => 'pending']);
        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 2, 'voucher_type' => 'sales', 'reference_no' => 'B', 'payload' => [], 'status' => 'synced']);

        $response = $this->getJson('/api/tally-sync/pending');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.reference_no', 'A');
    }

    public function test_a_token_without_the_tally_agent_ability_is_rejected(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['some-other-ability']);

        $this->getJson('/api/tally-sync/pending')->assertForbidden();
    }

    public function test_an_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/tally-sync/pending')->assertUnauthorized();
    }

    public function test_a_pending_poll_records_the_agent_checkin_time(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['tally-agent']);

        \Illuminate\Support\Facades\Cache::forget('tally_agent_last_checkin');
        $this->getJson('/api/tally-sync/pending')->assertOk();

        $this->assertNotNull(\Illuminate\Support\Facades\Cache::get('tally_agent_last_checkin'));
    }

    public function test_agent_can_acknowledge_a_successful_sync(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['tally-agent']);

        $queue = TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'A', 'payload' => [], 'status' => 'pending']);

        $response = $this->postJson("/api/tally-sync/{$queue->id}/acknowledge", [
            'status' => 'synced',
            'tally_voucher_id' => 'TALLY-VCH-99',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tally_sync_queue', [
            'id' => $queue->id,
            'status' => 'synced',
            'tally_voucher_id' => 'TALLY-VCH-99',
            'attempts' => 1,
        ]);
    }

    public function test_agent_can_acknowledge_a_failed_sync_with_an_error(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['tally-agent']);

        $queue = TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'A', 'payload' => [], 'status' => 'pending']);

        $this->postJson("/api/tally-sync/{$queue->id}/acknowledge", [
            'status' => 'failed',
            'error' => 'Ledger not found in Tally',
        ])->assertOk();

        $this->assertDatabaseHas('tally_sync_queue', [
            'id' => $queue->id,
            'status' => 'failed',
            'last_error' => 'Ledger not found in Tally',
            'attempts' => 1,
        ]);
    }
}
