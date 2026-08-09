<?php

namespace App\Services\Expenses;

use App\Models\ExpenseCategory;
use App\Repositories\Contracts\ExpenseCategoryRepositoryInterface;
use Illuminate\Support\Collection;

class ExpenseCategoryService
{
    public function __construct(private ExpenseCategoryRepositoryInterface $repository)
    {
    }

    public function listAll(): Collection
    {
        return $this->repository->all();
    }

    public function listActiveByType(string $type): Collection
    {
        return $this->repository->allActiveByType($type);
    }

    public function create(array $data): ExpenseCategory
    {
        return $this->repository->create([
            'name' => $data['name'],
            'type' => $data['type'],
            'party_model' => $data['party_model'] ?: null,
            'is_active' => true,
        ]);
    }

    public function toggleActive(int $id): ExpenseCategory
    {
        $category = $this->repository->find($id);

        if ($category === null) {
            throw new \InvalidArgumentException('Category not found.');
        }

        $category->is_active = ! $category->is_active;
        $this->repository->save($category);

        return $category;
    }
}
