<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_uses_datatables_not_listjs(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('DataTable(', false);
        $response->assertDontSee('list.min.js', false);
        $response->assertDontSee('list.pagination.js', false);
    }

    public function test_product_page_still_shows_all_products(): void
    {
        $user = User::factory()->create();
        \App\Models\Product::factory()->create(['name' => 'Table Migration Test Product']);

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('Table Migration Test Product');
    }
}
