<?php

namespace Tests\Feature\Inventory;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Invetry;
use App\Models\Product;
use App\Models\SparePartRequest;
use App\Services\Inventory\AvailabilityService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function product(): Product
    {
        return Product::create(['name' => 'Nozzle', 'rate' => '100', 'unit' => 'pcs', 'make' => 'X']);
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

    public function test_available_is_on_hand_minus_approved_requests(): void
    {
        $p = $this->product();
        Invetry::create(['product_id' => $p->id, 'quantity' => 10, 'vandername' => 'V', 'rate' => '100']);

        // approved reserves; pending/fulfilled/rejected do not reserve
        $this->makeRequest($p, 3, 'approved');
        $this->makeRequest($p, 5, 'pending');
        $this->makeRequest($p, 4, 'fulfilled');

        $svc = app(AvailabilityService::class);

        $this->assertSame(10, $svc->onHand($p->id));
        $this->assertSame(3, $svc->reserved($p->id));
        $this->assertSame(7, $svc->available($p->id));
    }

    public function test_snapshot_is_keyed_by_product(): void
    {
        $p = $this->product();
        Invetry::create(['product_id' => $p->id, 'quantity' => 8, 'vandername' => 'V', 'rate' => '100']);
        $this->makeRequest($p, 2, 'approved');

        $snap = app(AvailabilityService::class)->snapshot();

        $this->assertSame(8, $snap[$p->id]['on_hand']);
        $this->assertSame(2, $snap[$p->id]['reserved']);
        $this->assertSame(6, $snap[$p->id]['available']);
    }
}
