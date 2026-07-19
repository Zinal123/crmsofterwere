<?php

namespace App\Repositories\Contracts;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;

interface EmployeeRepositoryInterface
{
    public function all(): Collection;

    public function activeOnly(): Collection;

    public function find($id): ?Employee;

    public function create(array $data): Employee;

    public function update(Employee $employee, array $data): Employee;

    public function deactivate($id): Employee;
}
