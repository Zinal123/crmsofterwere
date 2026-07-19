<?php

namespace App\Repositories\Eloquent;

use App\Models\Attendance;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentAttendanceRepository implements AttendanceRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function upsertForDate(string $date, array $rows): void
    {
        foreach ($rows as $row) {
            Attendance::updateOrCreate(
                ['employee_id' => $row['employee_id'], 'date' => $date],
                [
                    'status' => $row['status'],
                    'overtime_hours' => $row['overtime_hours'] ?? 0,
                    'marked_by' => $row['marked_by'],
                ]
            );
        }
    }

    public function forDate(string $date): Collection
    {
        return $this->tenantScope->apply(Attendance::where('date', $date))->with('employee')->get();
    }

    public function forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection
    {
        return $this->tenantScope->apply(
            Attendance::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
        )->orderBy('date')->get();
    }
}
