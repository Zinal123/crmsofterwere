<?php

namespace App\Http\Controllers\Expenses;

use App\Http\Controllers\Controller;
use App\Services\Expenses\ExpenseCategoryService;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function __construct(private ExpenseCategoryService $service)
    {
    }

    public function index()
    {
        return view('expenses.categories.index', [
            'categories' => $this->service->listAll(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->service->create($data);

        return redirect()->route('admin.expense-categories.index')->with('success', 'Category added.');
    }

    public function toggle($id)
    {
        $this->service->toggleActive((int) $id);

        return redirect()->route('admin.expense-categories.index')->with('success', 'Category updated.');
    }

    public function quickAdd(Request $request)
    {
        $data = $this->validated($request);
        $category = $this->service->create($data);

        return response()->json(['category' => $category]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:payment,receipt',
            'party_model' => 'nullable|in:employee,vendor,client_account',
        ]);
    }
}
