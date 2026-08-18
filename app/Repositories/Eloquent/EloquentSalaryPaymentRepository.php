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

    public function forDateRange(?string $from, ?string $to): Collection
    {
        $query = $this->tenantScope->apply(SalaryPayment::with('employee'));

        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        } elseif ($from) {
            $query->where('date', '>=', $from);
        } elseif ($to) {
            $query->where('date', '<=', $to);
        }

        return $query->orderByDesc('date')->get();
    }

    public function totalForEmployeeAndMonth(int $employeeId, int $year, int $month): float
    {
        return (float) $this->tenantScope->apply(
            SalaryPayment::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
        )->sum('amount');
    }

    public function find($id): ?SalaryPayment
    {
        return SalaryPayment::find($id);
    }

    public function save(SalaryPayment $payment): void
    {
        $payment->save();
    }

    public function delete($id): void
    {
        SalaryPayment::find($id)?->delete();
    }
}
