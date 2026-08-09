<?php

namespace Tests\Feature\Expenses;

use App\Models\ExpenseCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_owner_can_view_the_category_master(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        ExpenseCategory::create(['name' => 'Electricity', 'type' => 'payment', 'party_model' => null]);

        $response = $this->actingAs($owner)->get(route('admin.expense-categories.index'));

        $response->assertOk();
        $response->assertSee('Electricity');
    }

    public function test_worker_cannot_view_the_category_master(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('admin.expense-categories.index'));

        $response->assertForbidden();
    }

    public function test_owner_can_add_a_payment_category_with_no_party(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->post(route('admin.expense-categories.store'), [
            'name' => 'Diesel / Fuel',
            'type' => 'payment',
            'party_model' => '',
        ]);

        $response->assertRedirect(route('admin.expense-categories.index'));
        $this->assertDatabaseHas('expense_categories', [
            'name' => 'Diesel / Fuel',
            'type' => 'payment',
            'party_model' => null,
            'is_active' => true,
        ]);
    }

    public function test_owner_can_add_a_category_that_requires_a_party(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->post(route('admin.expense-categories.store'), [
            'name' => 'Worker Wages',
            'type' => 'payment',
            'party_model' => 'employee',
        ]);

        $response->assertRedirect(route('admin.expense-categories.index'));
        $this->assertDatabaseHas('expense_categories', [
            'name' => 'Worker Wages',
            'party_model' => 'employee',
        ]);
    }

    public function test_owner_can_deactivate_a_category(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $category = ExpenseCategory::create(['name' => 'Rent', 'type' => 'payment', 'party_model' => null]);

        $response = $this->actingAs($owner)->post(route('admin.expense-categories.toggle', $category->id));

        $response->assertRedirect(route('admin.expense-categories.index'));
        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_quick_add_returns_json_for_the_transaction_forms_inline_add(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->postJson(route('admin.expense-categories.quick-add'), [
            'name' => 'Internet Bill',
            'type' => 'payment',
            'party_model' => '',
        ]);

        $response->assertOk();
        $response->assertJsonPath('category.name', 'Internet Bill');
        $this->assertDatabaseHas('expense_categories', ['name' => 'Internet Bill']);
    }
}
