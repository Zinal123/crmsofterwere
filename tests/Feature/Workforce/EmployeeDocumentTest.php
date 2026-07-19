<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_uploads_an_image_id_document(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $file = UploadedFile::fake()->image('aadhar.jpg', 1200, 800);

        $response = $this->actingAs($owner)->post(route('employees.documents.store', $employee->id), [
            'document_type' => 'aadhar',
            'document_number' => '1234-5678-9012',
            'document' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_documents', [
            'employee_id' => $employee->id,
            'document_type' => 'aadhar',
            'document_number' => '1234-5678-9012',
        ]);
    }

    public function test_owner_uploads_a_pdf_id_document(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $file = UploadedFile::fake()->create('pan.pdf', 200, 'application/pdf');

        $response = $this->actingAs($owner)->post(route('employees.documents.store', $employee->id), [
            'document_type' => 'pan',
            'document' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_documents', ['employee_id' => $employee->id, 'document_type' => 'pan']);
        $document = $employee->documents()->first();
        Storage::disk('public')->assertExists($document->path);
    }
}
