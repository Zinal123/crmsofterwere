<?php

namespace App\Repositories\Eloquent;

use App\Models\ExpenseCategory;
use App\Repositories\Contracts\ExpenseCategoryRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentExpenseCategoryRepository implements ExpenseCategoryRepositoryInterface
{
    public function all(): Collection
    {
        return ExpenseCategory::orderBy('type')->orderBy('name')->get();
    }

    public function allActive(): Collection
    {
        return ExpenseCategory::where('is_active', true)->orderBy('type')->orderBy('name')->get();
    }

    public function allActiveByType(string $type): Collection
    {
        return ExpenseCategory::where('is_active', true)->where('type', $type)->orderBy('name')->get();
    }

    public function find($id): ?ExpenseCategory
    {
        return ExpenseCategory::find($id);
    }

    public function create(array $data): ExpenseCategory
    {
        return ExpenseCategory::create($data);
    }

    public function save(ExpenseCategory $category): void
    {
        $category->save();
    }
}
