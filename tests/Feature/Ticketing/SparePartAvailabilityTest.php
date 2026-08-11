<?php

namespace Tests\Feature\Ticketing;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Invetry;
use App\Models\Product;
use App\Models\SparePartRequest;
use App\Services\Ticketing\SparePartRequestService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparePartAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Build a spare-part request with FK-valid related records.
     * spare_part_requests enforces FKs on client_machine_id and client_account_id,
     * and SQLite FK enforcement is ON in tests — so literal IDs would fail.
     */
    private function makeRequest(Product $p, int $quantity, string $status): SparePartRequest
    {
        $client = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create([
            'client_account_id' => $client->id,
            'product_id' => $p->id,
        ]);

        return SparePartRequest::create([
            'product_id' => $p->id,
            'quantity' => $quantity,
            'status' => $status,
            'client_machine_id' => $machine->id,
            'client_account_id' => $client->id,
        ]);
    }

    public function test_cannot_approve_more_than_is_available(): void
    {
        $p = Product::create(['name' => 'Lens', 'rate' => '100', 'unit' => 'pcs', 'make' => 'X']);
        Invetry::create(['product_id' => $p->id, 'quantity' => 2, 'vandername' => 'V', 'rate' => '100']);
        $req = $this->makeRequest($p, 5, 'pending');

        $this->expectException(\InvalidArgumentException::class);
        app(SparePartRequestService::class)->updateStatus($req, 'approved');
    }

    public function test_can_approve_within_available(): void
    {
        $p = Product::create(['name' => 'Lens', 'rate' => '100', 'unit' => 'pcs', 'make' => 'X']);
        Invetry::create(['product_id' => $p->id, 'quantity' => 5, 'vandername' => 'V', 'rate' => '100']);
        $req = $this->makeRequest($p, 3, 'pending');

        $updated = app(SparePartRequestService::class)->updateStatus($req, 'approved');
        $this->assertSame('approved', $updated->status);
    }
}
