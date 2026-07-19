<?php

namespace App\Repositories\Contracts;

use App\Models\SalaryPayment;
use Illuminate\Database\Eloquent\Collection;

interface SalaryPaymentRepositoryInterface
{
    public function create(array $data): SalaryPayment;

    public function forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection;

    public function totalForEmployeeAndMonth(int $employeeId, int $year, int $month): float;
}
