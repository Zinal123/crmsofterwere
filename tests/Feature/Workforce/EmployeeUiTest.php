<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_employees_with_pay_type(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        Employee::factory()->create(['name' => 'Vikram Singh', 'pay_type' => 'daily']);

        $response = $this->actingAs($owner)->get(route('employees.index'));

        $response->assertOk();
        $response->assertSee('Vikram Singh');
    }

    public function test_create_form_renders(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('employees.create'));

        $response->assertOk();
    }

    public function test_edit_form_shows_existing_values_and_document_upload(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['name' => 'Deepak Verma']);

        $response = $this->actingAs($owner)->get(route('employees.edit', $employee->id));

        $response->assertOk();
        $response->assertSee('Deepak Verma');
        $response->assertSee(route('employees.documents.store', $employee->id), false);
    }
}
