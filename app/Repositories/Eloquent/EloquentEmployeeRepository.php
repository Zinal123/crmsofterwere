<?php

namespace App\Repositories\Eloquent;

use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function all(): Collection
    {
        return $this->tenantScope->apply(Employee::orderBy('name'))->get();
    }

    public function activeOnly(): Collection
    {
        return $this->tenantScope->apply(Employee::where('is_active', true)->orderBy('name'))->get();
    }

    public function find($id): ?Employee
    {
        return $this->tenantScope->apply(Employee::query())->find($id);
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        return $employee;
    }

    public function deactivate($id): Employee
    {
        $employee = Employee::findOrFail($id);
        $employee->is_active = false;
        $employee->save();

        return $employee;
    }
}
