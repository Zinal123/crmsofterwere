<?php

namespace App\Repositories\Eloquent;

use App\Models\SalaryPayment;
use App\Repositories\Contracts\SalaryPaymentRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentSalaryPaymentRepository implements SalaryPaymentRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function create(array $data): SalaryPayment
    {
        return SalaryPayment::create($data);
    }

    public function forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection
    {
        return $this->tenantScope->apply(
            SalaryPayment::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
        )->orderBy('date')->get();
    }

    public function totalForEmployeeAndMonth(int $employeeId, int $year, int $month): float
    {
        return (float) $this->tenantScope->apply(
            SalaryPayment::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
        )->sum('amount');
    }
}
