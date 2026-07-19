<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceModelsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_workforce_tables_migrate_and_relate_correctly(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'overtime_hours' => 2,
            'marked_by' => $user->id,
        ]);

        $payment = SalaryPayment::create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'amount' => 1000,
            'paid_by' => $user->id,
        ]);

        $document = EmployeeDocument::create([
            'employee_id' => $employee->id,
            'document_type' => 'aadhar',
            'path' => 'employee-documents/test.jpg',
            'uploaded_by' => $user->id,
        ]);

        $this->assertTrue($employee->attendances->first()->is($attendance));
        $this->assertTrue($employee->salaryPayments->first()->is($payment));
        $this->assertTrue($employee->documents->first()->is($document));
    }

    public function test_attendance_is_unique_per_employee_per_day(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-19', 'status' => 'present', 'marked_by' => $user->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-19', 'status' => 'absent', 'marked_by' => $user->id]);
    }
}
