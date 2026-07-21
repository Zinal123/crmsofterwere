<?php

namespace Tests\Feature;

use App\Models\Invetry;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_inventory_row_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $item = Invetry::create(['product_id' => 1, 'quantity' => 10]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'inventory', 'id' => $item->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_updating_quantity_logs_the_old_and_new_value(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $item = Invetry::create(['product_id' => 1, 'quantity' => 10]);

        $item->quantity = 4;
        $item->save();

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'inventory', 'id' => $item->id]));

        $response->assertOk();
        $response->assertSee('10');
        $response->assertSee('4');
    }

    public function test_inventory_list_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $item = Invetry::create(['product_id' => 1, 'quantity' => 5]);

        $response = $this->actingAs($owner)->get(route('invoice.inventrylist'));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $item->id . '"', false);
    }
}
