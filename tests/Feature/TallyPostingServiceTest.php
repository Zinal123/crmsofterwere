<?php

namespace Tests\Feature;

use App\Models\TallyConnection;
use App\Models\TallySyncQueue;
use App\Services\Integration\TallyPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TallyPostingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function connection(string $status = 'connected'): TallyConnection
    {
        return TallyConnection::create([
            'host' => '192.168.1.50', 'port' => 9000, 'company_name' => 'Oracle Machine Tech',
            'username' => 'admin', 'password' => 'secret', 'status' => $status,
        ]);
    }

    private function queue(): TallySyncQueue
    {
        return TallySyncQueue::create([
            'source_type' => 'App\\Models\\Invoice',
            'source_id' => 1,
            'voucher_type' => 'sales',
            'reference_no' => 'SALES-1',
            'payload' => [
                'invoice_id' => 1, 'invoice_number' => 'INV-001', 'invoice_date' => '2026-09-22',
                'customer' => ['name' => 'Test Customer'],
                'total_before_tax' => 50000.0, 'total_with_tax' => 59000.0,
                'sgst_amount' => 4500.0, 'cgst_amount' => 4500.0, 'igst_amount' => 0.0,
                'line_items' => [[
                    'product_name' => 'Widget X', 'hsn' => '8456', 'unit' => 'Nos',
                    'quantity' => 1, 'rate' => 50000, 'gst_percent' => 18,
                    'gst_amount' => 9000, 'total_amount' => 50000, 'taxable_amount' => 50000.0,
                ]],
            ],
            'status' => 'pending',
        ]);
    }

    public function test_it_marks_synced_when_tally_accepts_the_voucher(): void
    {
        $this->connection();
        $queue = $this->queue();

        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<RESPONSE><CREATED>1</CREATED><ERRORS>0</ERRORS><LASTVCHID>999</LASTVCHID></RESPONSE>', 200),
        ]);

        app(TallyPostingService::class)->attempt($queue);
        $queue->refresh();

        $this->assertSame('synced', $queue->status);
        $this->assertSame('999', $queue->tally_voucher_id);
        $this->assertSame(1, $queue->attempts);
    }

    public function test_it_marks_failed_with_tallys_error_message(): void
    {
        $this->connection();
        $queue = $this->queue();

        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<RESPONSE><CREATED>0</CREATED><ERRORS>1</ERRORS><LINEERROR>Ledger Test Customer does not exist</LINEERROR></RESPONSE>', 200),
        ]);

        app(TallyPostingService::class)->attempt($queue);
        $queue->refresh();

        $this->assertSame('failed', $queue->status);
        $this->assertSame('Ledger Test Customer does not exist', $queue->last_error);
        $this->assertSame(1, $queue->attempts);
    }

    public function test_a_network_failure_does_not_count_against_the_attempt_cap(): void
    {
        $this->connection();
        $queue = $this->queue();

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('timed out');
        });

        app(TallyPostingService::class)->attempt($queue);
        $queue->refresh();

        $this->assertSame('pending', $queue->status);
        $this->assertSame(0, $queue->attempts);
        $this->assertStringContainsString('Could not reach Tally server', (string) $queue->last_error);
        $this->assertStringContainsString('timed out', (string) $queue->last_error);
    }

    public function test_it_leaves_the_row_pending_when_no_connection_is_configured(): void
    {
        $queue = $this->queue();

        Http::fake();
        app(TallyPostingService::class)->attempt($queue);

        Http::assertNothingSent();
        $queue->refresh();
        $this->assertSame('pending', $queue->status);
        $this->assertSame(0, $queue->attempts);
    }

    public function test_it_leaves_the_row_pending_when_the_connection_is_not_yet_verified(): void
    {
        $this->connection('disconnected');
        $queue = $this->queue();

        Http::fake();
        app(TallyPostingService::class)->attempt($queue);

        Http::assertNothingSent();
        $queue->refresh();
        $this->assertSame('pending', $queue->status);
    }

    public function test_it_does_not_retry_a_row_that_already_hit_the_attempt_cap(): void
    {
        $this->connection();
        $queue = $this->queue();
        $queue->update(['status' => 'failed', 'attempts' => 5]);

        Http::fake();
        app(TallyPostingService::class)->attempt($queue);

        Http::assertNothingSent();
        $queue->refresh();
        $this->assertSame(5, $queue->attempts);
    }

    public function test_it_skips_an_already_synced_row(): void
    {
        $this->connection();
        $queue = $this->queue();
        $queue->update(['status' => 'synced', 'attempts' => 1]);

        Http::fake();
        app(TallyPostingService::class)->attempt($queue);

        Http::assertNothingSent();
    }
}
