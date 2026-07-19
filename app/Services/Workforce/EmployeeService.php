<?php

namespace App\Services\Workforce;

use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EmployeeService
{
    public function __construct(private EmployeeRepositoryInterface $repository)
    {
    }

    public function list(): Collection
    {
        return $this->repository->all();
    }

    public function activeList(): Collection
    {
        return $this->repository->activeOnly();
    }

    public function find($id): ?Employee
    {
        return $this->repository->find($id);
    }

    public function create(array $data): Employee
    {
        $data['is_active'] = true;

        return $this->repository->create($data);
    }

    public function update($id, array $data): Employee
    {
        $employee = $this->repository->find($id);

        return $this->repository->update($employee, $data);
    }

    public function deactivate($id): Employee
    {
        return $this->repository->deactivate($id);
    }
}
