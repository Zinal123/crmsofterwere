<?php

namespace App\Http\Controllers\Expenses;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Services\Expenses\DailyTransactionService;
use App\Services\Expenses\ExpenseCategoryService;
use App\Services\Vendor\VendorService;
use App\Services\Workforce\EmployeeService;
use Illuminate\Http\Request;

class DailyTransactionController extends Controller
{
    public function __construct(
        private DailyTransactionService $service,
        private ExpenseCategoryService $categoryService,
        private EmployeeService $employeeService,
        private VendorService $vendorService,
    ) {
    }

    public function index()
    {
        return view('expenses.index', [
            'transactions' => $this->service->listAll(),
            'paymentCategories' => $this->categoryService->listActiveByType('payment'),
            'receiptCategories' => $this->categoryService->listActiveByType('receipt'),
            'employees' => $this->employeeService->activeList(),
            'vendors' => $this->vendorService->listAll(),
        ]);
    }

    public function cashBook()
    {
        return view('expenses.cashbook', [
            'transactions' => $this->service->cashBook(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:payment,receipt',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_mode' => 'required|in:cash,bank,upi,cheque',
            'description' => 'nullable|string|max:2000',
            'receipt_photo' => 'nullable|image|max:5120',
        ]);

        $category = ExpenseCategory::findOrFail($data['expense_category_id']);

        if ($category->party_model === 'employee') {
            $request->validate(['employee_id' => 'required|exists:employees,id']);
            $data['employee_id'] = $request->input('employee_id');
        } elseif ($category->party_model === 'vendor') {
            $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'vendor_bill_id' => 'nullable|exists:vendor_bills,id',
            ]);
            $data['vendor_id'] = $request->input('vendor_id');
            $data['vendor_bill_id'] = $request->input('vendor_bill_id');
        } elseif ($category->party_model === 'client_account') {
            $request->validate(['client_account_id' => 'required|exists:client_accounts,id']);
            $data['client_account_id'] = $request->input('client_account_id');
        }

        $data['receipt_photo'] = $request->file('receipt_photo');

        try {
            $this->service->create($category, $data, $request->user());
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('expenses.index')->with('success', 'Transaction recorded.');
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return redirect()->route('expenses.index')->with('success', 'Transaction deleted.');
    }
}
