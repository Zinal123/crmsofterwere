<?php

namespace Tests\Feature;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Invetry;
use App\Models\Product;
use App\Models\SparePartRequest;
use App\Services\Inventory\SparePartAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparePartAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeRequest(Product $part, int $quantity, string $status): SparePartRequest
    {
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);

        return SparePartRequest::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'product_id' => $part->id,
            'quantity' => $quantity,
            'status' => $status,
        ]);
    }

    public function test_available_equals_on_hand_when_there_are_no_other_requests(): void
    {
        $part = Product::factory()->create();
        Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 10]);

        $available = app(SparePartAvailabilityService::class)->available($part->id);

        $this->assertSame(10, $available);
    }

    public function test_available_is_reduced_by_other_pending_and_approved_requests(): void
    {
        $part = Product::factory()->create();
        Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 10]);
        $this->makeRequest($part, 3, 'pending');
        $this->makeRequest($part, 4, 'approved');

        $available = app(SparePartAvailabilityService::class)->available($part->id);

        $this->assertSame(3, $available);
    }

    public function test_fulfilled_and_rejected_requests_do_not_reduce_availability(): void
    {
        $part = Product::factory()->create();
        Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 10]);
        $this->makeRequest($part, 5, 'fulfilled');
        $this->makeRequest($part, 5, 'rejected');

        $available = app(SparePartAvailabilityService::class)->available($part->id);

        $this->assertSame(10, $available);
    }

    public function test_a_pending_request_can_exclude_itself_from_the_reservation_count(): void
    {
        $part = Product::factory()->create();
        Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 10]);
        $request = $this->makeRequest($part, 6, 'pending');

        $availableIncludingSelf = app(SparePartAvailabilityService::class)->available($part->id);
        $availableExcludingSelf = app(SparePartAvailabilityService::class)->available($part->id, $request->id);

        $this->assertSame(4, $availableIncludingSelf);
        $this->assertSame(10, $availableExcludingSelf);
    }

    public function test_available_is_zero_not_negative_when_over_reserved(): void
    {
        $part = Product::factory()->create();
        Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 5]);
        $this->makeRequest($part, 8, 'pending');

        $available = app(SparePartAvailabilityService::class)->available($part->id);

        $this->assertSame(0, $available);
    }

    public function test_available_is_zero_when_no_inventory_record_exists(): void
    {
        $part = Product::factory()->create();

        $available = app(SparePartAvailabilityService::class)->available($part->id);

        $this->assertSame(0, $available);
    }
}
