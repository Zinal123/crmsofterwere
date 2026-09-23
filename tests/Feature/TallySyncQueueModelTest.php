<?php

namespace Tests\Feature;

use App\Models\TallySyncQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallySyncQueueModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_and_casts_the_payload_as_an_array(): void
    {
        $queue = TallySyncQueue::create([
            'source_type' => 'App\\Models\\Invoice',
            'source_id' => 42,
            'voucher_type' => 'sales',
            'reference_no' => 'SALES-42',
            'payload' => ['invoice_number' => 'INV-001', 'total_with_tax' => 59000.0],
            'status' => 'pending',
        ]);

        $fresh = TallySyncQueue::find($queue->id);

        $this->assertSame('pending', $fresh->status);
        $this->assertIsArray($fresh->payload);
        $this->assertSame('INV-001', $fresh->payload['invoice_number']);
    }

    public function test_pending_and_failed_scopes_filter_by_status(): void
    {
        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'A', 'payload' => [], 'status' => 'pending']);
        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 2, 'voucher_type' => 'sales', 'reference_no' => 'B', 'payload' => [], 'status' => 'failed']);
        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 3, 'voucher_type' => 'sales', 'reference_no' => 'C', 'payload' => [], 'status' => 'synced']);

        $this->assertSame(['A'], TallySyncQueue::pending()->pluck('reference_no')->all());
        $this->assertSame(['B'], TallySyncQueue::failed()->pluck('reference_no')->all());
    }
}
