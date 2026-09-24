<?php

namespace Tests\Feature;

use App\Models\TallyConnection;
use App\Models\TallySyncQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TallySyncPendingCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_retries_pending_rows_and_skips_over_cap_failed_rows(): void
    {
        TallyConnection::create([
            'host' => '192.168.1.50', 'port' => 9000, 'company_name' => 'Oracle Machine Tech',
            'username' => 'admin', 'password' => 'secret', 'status' => 'connected',
        ]);

        $pending = TallySyncQueue::create([
            'source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'A',
            'payload' => [
                'invoice_id' => 1, 'invoice_number' => 'INV-1', 'invoice_date' => '2026-09-22',
                'customer' => ['name' => 'Test'], 'total_before_tax' => 100.0, 'total_with_tax' => 118.0,
                'sgst_amount' => 9.0, 'cgst_amount' => 9.0, 'igst_amount' => 0.0, 'line_items' => [],
            ],
            'status' => 'pending',
        ]);
        $overCap = TallySyncQueue::create([
            'source_type' => 'x', 'source_id' => 2, 'voucher_type' => 'sales', 'reference_no' => 'B',
            'payload' => [], 'status' => 'failed', 'attempts' => 5,
        ]);

        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<RESPONSE><CREATED>1</CREATED><ERRORS>0</ERRORS><LASTVCHID>1</LASTVCHID></RESPONSE>', 200),
        ]);

        $this->artisan('tally:sync-pending')->assertExitCode(0);

        $pending->refresh();
        $overCap->refresh();

        $this->assertSame('synced', $pending->status);
        $this->assertSame('failed', $overCap->status);
        $this->assertSame(5, $overCap->attempts);
    }
}
