<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_create_validates_required_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('productstore'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_product_create_succeeds_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('productstore'), [
            'name' => 'Test Laser Cutter',
            'rate' => 500000,
            'unit' => 'Nos',
            'make' => 'TEST-1',
        ]);

        $response->assertRedirect(route('product'));
        $this->assertDatabaseHas('product', ['name' => 'Test Laser Cutter']);
    }
}
