<?php

namespace Tests\Feature\Tickets;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Product;
use App\Models\SparePartRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparePartRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_owner_can_mark_a_product_as_a_spare_part(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->create();
        $this->assertFalse((bool) $product->fresh()->is_spare_part);

        $response = $this->actingAs($owner)->post(route('product.toggle-spare-part', $product->id));

        $response->assertRedirect(route('product'));
        $this->assertTrue($product->fresh()->is_spare_part);
    }

    public function test_client_only_sees_products_marked_as_spare_parts_on_the_request_form(): void
    {
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        Product::factory()->create(['name' => 'A Real Spare Part', 'is_spare_part' => true]);
        Product::factory()->create(['name' => 'The Whole Machine', 'is_spare_part' => false]);

        $response = $this->actingAs($account, 'client')->get(route('client.spare-parts.create', $machine->id));

        $response->assertOk();
        $response->assertSee('A Real Spare Part');
        $response->assertDontSee('The Whole Machine');
    }

    public function test_client_can_submit_a_spare_part_request_for_their_own_machine(): void
    {
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $part = Product::factory()->create(['is_spare_part' => true]);

        $response = $this->actingAs($account, 'client')->post(route('client.spare-parts.store'), [
            'client_machine_id' => $machine->id,
            'product_id' => $part->id,
            'quantity' => 2,
            'note' => 'Urgent please',
        ]);

        $response->assertRedirect(route('client.spare-parts.index'));
        $this->assertDatabaseHas('spare_part_requests', [
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'product_id' => $part->id,
            'quantity' => 2,
            'note' => 'Urgent please',
            'status' => 'pending',
        ]);
    }

    public function test_client_cannot_request_a_spare_part_for_someone_elses_machine(): void
    {
        $account = ClientAccount::factory()->create();
        $otherAccount = ClientAccount::factory()->create();
        $otherMachine = ClientMachine::factory()->create(['client_account_id' => $otherAccount->id]);
        $part = Product::factory()->create(['is_spare_part' => true]);

        $response = $this->actingAs($account, 'client')->post(route('client.spare-parts.store'), [
            'client_machine_id' => $otherMachine->id,
            'product_id' => $part->id,
            'quantity' => 1,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('spare_part_requests', ['client_machine_id' => $otherMachine->id]);
    }

    public function test_client_request_list_shows_only_their_own_requests(): void
    {
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $part = Product::factory()->create(['name' => 'My Own Nozzle', 'is_spare_part' => true]);
        SparePartRequest::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'product_id' => $part->id,
            'quantity' => 1,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($account, 'client')->get(route('client.spare-parts.index'));

        $response->assertOk();
        $response->assertSee('My Own Nozzle');
    }

    public function test_owner_can_view_the_spare_part_request_inbox_and_update_status(): void
    {
        $owner = User::factory()->create();
        $account = ClientAccount::factory()->create(['name' => 'Solanki Fabricators']);
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        $part = Product::factory()->create(['is_spare_part' => true]);
        $sparePartRequest = SparePartRequest::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'product_id' => $part->id,
            'quantity' => 3,
            'status' => 'pending',
        ]);

        $indexResponse = $this->actingAs($owner)->get(route('admin.spare-part-requests.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Solanki Fabricators');

        $updateResponse = $this->actingAs($owner)->post(route('admin.spare-part-requests.update-status', $sparePartRequest->id), [
            'status' => 'fulfilled',
        ]);

        $updateResponse->assertRedirect(route('admin.spare-part-requests.index'));
        $this->assertSame('fulfilled', $sparePartRequest->fresh()->status);
    }

    public function test_worker_cannot_reach_the_spare_part_request_inbox(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('admin.spare-part-requests.index'));

        $response->assertStatus(403);
    }
}
