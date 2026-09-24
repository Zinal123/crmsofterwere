<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TallyConnection;
use App\Models\User;
use App\Services\Integration\TallyPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TallyInvoicePostingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function postInvoice(User $user, Product $product): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)->post(route('invoice.store'), [
            'invoice_id' => 'INV-TALLY-POST-001',
            'invoice_date' => '2026-09-22',
            'placesupply' => 'Gujarat',
            'billing_address_full_name' => 'Tally Post Test Customer',
            'billing_state' => 'Gujarat',
            'shipping_state' => 'Gujarat',
            'order_summary_cart_total' => 59000,
            'order_summary_cart_amount' => 50000,
            'new_product_obj' => [
                [
                    'product_name' => $product->id,
                    'hsn' => '8456',
                    'unit' => 'Nos',
                    'product_rate' => 50000,
                    'product_qty' => 1,
                    'product_price' => 50000,
                    'gst' => 18,
                    'withtax' => 9000,
                    'total' => 59000,
                ],
            ],
        ]);
    }

    public function test_invoice_creation_still_succeeds_with_no_tally_connection_configured(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->postInvoice($user, $product);

        $response->assertOk();
        $this->assertDatabaseHas('tally_sync_queue', ['status' => 'pending']);
    }

    public function test_invoice_posts_to_tally_immediately_when_a_connection_is_verified(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        TallyConnection::create([
            'host' => '192.168.1.50', 'port' => 9000, 'company_name' => 'Oracle Machine Tech',
            'username' => 'admin', 'password' => 'secret', 'status' => 'connected',
        ]);

        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<RESPONSE><CREATED>1</CREATED><ERRORS>0</ERRORS><LASTVCHID>1</LASTVCHID></RESPONSE>', 200),
        ]);

        $response = $this->postInvoice($user, $product);

        $response->assertOk();
        $this->assertDatabaseHas('tally_sync_queue', ['status' => 'synced', 'tally_voucher_id' => '1']);
    }

    public function test_invoice_creation_still_succeeds_when_tally_is_unreachable(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        TallyConnection::create([
            'host' => '192.168.1.50', 'port' => 9000, 'company_name' => 'Oracle Machine Tech',
            'username' => 'admin', 'password' => 'secret', 'status' => 'connected',
        ]);

        Http::fake(function () {
            throw new ConnectionException('timed out');
        });

        $response = $this->postInvoice($user, $product);

        $response->assertOk();
        $this->assertDatabaseHas('tally_sync_queue', ['status' => 'failed']);
    }

    /**
     * The test above ("...when_tally_is_unreachable") only proves that a
     * ConnectionException thrown inside Http::fake() survives - but that
     * exception never reaches InvoiceService's own try/catch, because
     * TallyClient::post() already absorbs it internally and normalizes it
     * into a TallyResponse with accepted = false. This test instead forces
     * a \Throwable to escape TallyPostingService::attempt() itself, which
     * is the only way to actually exercise InvoiceService's try/catch
     * around attempt().
     */
    public function test_invoice_creation_still_succeeds_when_tally_posting_service_itself_throws(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->mock(TallyPostingService::class, function ($mock) {
            $mock->shouldReceive('attempt')->once()->andThrow(new \RuntimeException('Simulated internal Tally posting failure'));
        });

        $response = $this->postInvoice($user, $product);

        $response->assertOk();
        $this->assertDatabaseHas('invoice', ['invoice_id' => 'INV-TALLY-POST-001']);
    }
}
