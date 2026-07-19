<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface
{
    public function upsertForDate(string $date, array $rows): void;

    public function forDate(string $date): Collection;

    public function forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection;
}
