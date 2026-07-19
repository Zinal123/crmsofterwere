<?php

namespace App\Services\Workforce;

use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AttendanceService
{
    public function __construct(private AttendanceRepositoryInterface $repository)
    {
    }

    public function markForDate(string $date, array $rows, User $marker): void
    {
        $rows = array_map(function ($row) use ($marker) {
            $row['marked_by'] = $marker->id;

            return $row;
        }, $rows);

        $this->repository->upsertForDate($date, $rows);
    }

    public function forDate(string $date): Collection
    {
        return $this->repository->forDate($date);
    }

    public function forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection
    {
        return $this->repository->forEmployeeAndMonth($employeeId, $year, $month);
    }
}
