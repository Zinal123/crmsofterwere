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

    public function test_owner_uploads_a_document_near_the_apps_stated_10mb_limit(): void
    {
        // Regression test: the app's own validation rule (max:10240 KB)
        // already allowed this; the real-world bug was the server's
        // upload_max_filesize/post_max_size (php.ini, public/.user.ini for
        // production) being lower than what this rule advertises. That part
        // can't be exercised from PHPUnit (Laravel's test client doesn't go
        // through real HTTP/php.ini enforcement) - verified separately via a
        // real multipart request against the running dev server. This test
        // locks in that the application-level rule itself genuinely accepts
        // a file at that size, not just files well under it.
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        // A PDF, not an image: PDFs are stored as-is (see
        // App\Services\Workforce\EmployeeDocumentService), never run through
        // GD compression, so a synthetic ->create() fixture (junk bytes, not
        // real image data) is safe to use here at a realistic size.
        $file = UploadedFile::fake()->create('id-scan.pdf', 9000, 'application/pdf');

        $response = $this->actingAs($owner)->post(route('employees.documents.store', $employee->id), [
            'document_type' => 'aadhar',
            'document' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_documents', ['employee_id' => $employee->id, 'document_type' => 'aadhar']);
    }

    public function test_employee_edit_page_links_each_document_to_view_it(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $employee->documents()->create([
            'document_type' => 'aadhar',
            'document_number' => '1234-5678-9012',
            'path' => 'employee-documents/' . $employee->id . '/doc_test.jpg',
            'uploaded_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->get(route('employees.edit', $employee->id));

        $response->assertOk();
        $response->assertSee(asset('storage/employee-documents/' . $employee->id . '/doc_test.jpg'), false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_owner_uploads_a_document_over_the_10mb_limit_gets_a_clean_422(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $file = UploadedFile::fake()->create('too-big.jpg', 11000, 'image/jpeg');

        $response = $this->actingAs($owner)->postJson(route('employees.documents.store', $employee->id), [
            'document_type' => 'aadhar',
            'document' => $file,
        ]);

        $response->assertStatus(422);
    }
}
