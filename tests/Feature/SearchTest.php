<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        return $owner;
    }

    public function test_short_query_returns_no_results(): void
    {
        $response = $this->actingAs($this->owner())->getJson(route('search.results', ['q' => 'a']));

        $response->assertOk();
        $response->assertJson(['results' => []]);
    }

    public function test_finds_matching_product_by_name(): void
    {
        Product::factory()->create(['name' => 'Fiber Laser Cutting Machine 1500W']);

        $response = $this->actingAs($this->owner())->getJson(route('search.results', ['q' => 'Fiber Laser']));

        $response->assertOk();
        $response->assertJsonPath('results.Products.0.title', 'Fiber Laser Cutting Machine 1500W');
    }

    public function test_finds_matching_invoice_by_customer_name(): void
    {
        $invoice = Invoice::factory()->create();
        Customer::factory()->create(['invoice_id' => $invoice->id, 'name' => 'Rajasthan Metal Works']);

        $response = $this->actingAs($this->owner())->getJson(route('search.results', ['q' => 'Rajasthan']));

        $response->assertOk();
        $response->assertJsonPath('results.Invoices.0.subtitle', 'Rajasthan Metal Works');
    }

    public function test_finds_matching_employee_by_name(): void
    {
        Employee::factory()->create(['name' => 'Mustafa Krish']);

        $response = $this->actingAs($this->owner())->getJson(route('search.results', ['q' => 'Mustafa']));

        $response->assertOk();
        $response->assertJsonPath('results.Employees.0.title', 'Mustafa Krish');
    }

    public function test_worker_without_employee_manage_permission_does_not_see_employee_results(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        Employee::factory()->create(['name' => 'Mustafa Krish']);

        $response = $this->actingAs($worker)->getJson(route('search.results', ['q' => 'Mustafa']));

        $response->assertOk();
        $response->assertJsonMissingPath('results.Employees');
    }

    public function test_no_matches_returns_empty_results_object(): void
    {
        $response = $this->actingAs($this->owner())->getJson(route('search.results', ['q' => 'zzzznomatch']));

        $response->assertOk();
        $response->assertJson(['results' => []]);
    }
}
