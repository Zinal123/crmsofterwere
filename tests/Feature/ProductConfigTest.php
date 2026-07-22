<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_cuttingstore_saves_the_company_name(): void
    {
        // Regression test: the form field was named `companyname`, but
        // Lasercutting::$fillable (and the addlaser table column) is
        // `company` - the mismatch meant every laser-cutting config ever
        // saved through this form silently lost the company name.
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(route('cuttingstore'), [
            'product_id' => $product->id,
            'company' => 'Test Laser Co',
            'modal' => 'Test Model',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('addlaser', [
            'product_id' => $product->id,
            'company' => 'Test Laser Co',
            'modal' => 'Test Model',
        ]);
    }

    public function test_cuttingstore_rejects_a_fully_empty_submission(): void
    {
        // Regression test: a POST with no product_id previously created an
        // all-NULL row invisible under any product's own config list.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cuttingstore'), []);

        $response->assertStatus(422);
        $this->assertDatabaseCount('addlaser', 0);
    }

    public function test_softerwerestore_rejects_a_missing_product_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('softerwerestore'), [
            'company' => 'No Product Software Co',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('softwaredetails', ['company' => 'No Product Software Co']);
    }
}
