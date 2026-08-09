<?php

namespace App\Repositories\Contracts;

use App\Models\ExpenseCategory;
use Illuminate\Support\Collection;

interface ExpenseCategoryRepositoryInterface
{
    public function all(): Collection;

    public function allActive(): Collection;

    public function allActiveByType(string $type): Collection;

    public function find($id): ?ExpenseCategory;

    public function create(array $data): ExpenseCategory;

    public function save(ExpenseCategory $category): void;
}
