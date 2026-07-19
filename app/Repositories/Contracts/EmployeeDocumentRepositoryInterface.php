<?php

namespace App\Repositories\Contracts;

use App\Models\EmployeeDocument;

interface EmployeeDocumentRepositoryInterface
{
    public function create(array $data): EmployeeDocument;
}
