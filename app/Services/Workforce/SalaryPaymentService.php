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

    public function forDateRange(?string $from, ?string $to): Collection
    {
        return $this->repository->forDateRange($from, $to);
    }

    public function update($id, array $data): SalaryPayment
    {
        $payment = $this->repository->find($id);

        if ($payment === null) {
            throw new \InvalidArgumentException('Salary payment not found.');
        }

        $payment->fill($data);
        $this->repository->save($payment);

        return $payment;
    }

    public function delete($id): void
    {
        $this->repository->delete($id);
    }
}
