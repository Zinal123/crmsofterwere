<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_shows_the_employees_audit_trail(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['name' => 'Trail Employee']);
        $employee->update(['department' => 'Fabrication']);

        $response = $this->actingAs($owner)->get(route('employees.edit', $employee->id));

        $response->assertOk();
        $response->assertSee('department');
        $response->assertSee('Fabrication');
    }

    public function test_edit_page_history_includes_document_upload_events(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['name' => 'Trail Employee']);
        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'document_type' => 'aadhar',
            'document_number' => '1234-5678-9012',
            'path' => 'employee-documents/' . $employee->id . '/doc.jpg',
            'uploaded_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->get(route('employees.edit', $employee->id));

        $response->assertOk();
        // One "created" row for the employee, one for the document upload.
        $this->assertEquals(2, substr_count($response->getContent(), '<td>created</td>'));
    }
}
