<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Softerwere;
use App\Models\Termandcondition;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_product_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $product = Product::create(['name' => 'Test Machine', 'rate' => 100000]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'product', 'id' => $product->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_deleting_a_product_logs_a_deleted_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $product = Product::create(['name' => 'To Delete']);
        $product->delete();

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'product', 'id' => $product->id]));

        $response->assertOk();
        $response->assertSee('deleted');
    }

    public function test_creating_a_product_config_row_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $product = Product::create(['name' => 'Config Parent']);
        $software = Softerwere::create(['product_id' => $product->id]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'softerwere', 'id' => $software->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_creating_a_term_and_condition_row_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $term = Termandcondition::create([]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'termandcondition', 'id' => $term->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_product_list_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $product = Product::create(['name' => 'List Row Product']);

        $response = $this->actingAs($owner)->get(route('product'));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $product->id . '"', false);
    }
}
