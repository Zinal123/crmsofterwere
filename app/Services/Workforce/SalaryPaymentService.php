<?php

namespace App\Services\Workforce;

use App\Models\SalaryPayment;
use App\Repositories\Contracts\SalaryPaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SalaryPaymentService
{
    public function __construct(private SalaryPaymentRepositoryInterface $repository)
    {
    }

    public function create(array $data): SalaryPayment
    {
        return $this->repository->create($data);
    }

    public function forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection
    {
        return $this->repository->forEmployeeAndMonth($employeeId, $year, $month);
    }
}
