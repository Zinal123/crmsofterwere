<?php

namespace App\Repositories\Eloquent;

use App\Models\EmployeeDocument;
use App\Repositories\Contracts\EmployeeDocumentRepositoryInterface;

class EloquentEmployeeDocumentRepository implements EmployeeDocumentRepositoryInterface
{
    public function create(array $data): EmployeeDocument
    {
        return EmployeeDocument::create($data);
    }
}
