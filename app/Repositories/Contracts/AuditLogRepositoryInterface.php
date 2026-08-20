<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface AuditLogRepositoryInterface
{
    public function record(Model $model, string $action, ?string $fieldName = null, $oldValue = null, $newValue = null): void;

    public function forRecord(string $type, int $id): Collection;

    /** @param string[] $types */
    public function paginateForTypes(array $types, ?string $typeFilter = null): LengthAwarePaginator;
}
