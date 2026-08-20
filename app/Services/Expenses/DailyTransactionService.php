<?php

namespace App\Services\Expenses;

use App\Models\DailyTransaction;
use App\Models\Employee;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Workforce\SalaryPaymentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class DailyTransactionService
{
    public function __construct(
        private SalaryPaymentService $salaryPaymentService,
        private VendorPayableService $vendorPayableService,
    ) {
    }

    public function listAll(?string $from = null, ?string $to = null, ?string $type = null, bool $excludeLinkedMirrors = false, ?int $categoryId = null): Collection
    {
        $query = DailyTransaction::with('category')->orderByDesc('date')->orderByDesc('id');

        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        } elseif ($from) {
            $query->where('date', '>=', $from);
        } elseif ($to) {
            $query->where('date', '<=', $to);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($excludeLinkedMirrors) {
            $query->whereNull('linked_type');
        }

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        return $query->get();
    }

    /**
     * Chronological ledger (oldest first) with a running balance - the
     * Tally/Vyapar Cash Book pattern from the market research: receipts add,
     * payments subtract, shown as one running total across everything.
     */
    public function cashBook(): Collection
    {
        $balance = 0;

        return DailyTransaction::with('category')
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->map(function (DailyTransaction $transaction) use (&$balance) {
                $balance += $transaction->type === 'receipt' ? (float) $transaction->amount : -(float) $transaction->amount;
                $transaction->running_balance = round($balance, 2);

                return $transaction;
            })
            ->reverse()
            ->values();
    }

    /**
     * The category-driven routing described in the module plan: a category
     * with no party_model is a plain standalone line; one that requires a
     * party posts through that party's own owning module (payroll for an
     * Employee, the vendor payable ledger for a Vendor) and this row only
     * links to the real record created there - never a second, disconnected
     * figure. See ExpenseCategory::party_model.
     */
    public function create(ExpenseCategory $category, array $data, User $creator): DailyTransaction
    {
        $partyType = $category->party_model;
        $partyId = null;
        $linkedType = null;
        $linkedId = null;

        if ($partyType === 'employee') {
            $employee = Employee::find($data['employee_id'] ?? null);
            if ($employee === null) {
                throw new \InvalidArgumentException('An employee must be selected for a Worker Wages entry.');
            }

            $salaryPayment = $this->salaryPaymentService->create([
                'employee_id' => $employee->id,
                'date' => $data['date'],
                'amount' => $data['amount'],
                'note' => $data['description'] ?? null,
                'paid_by' => $creator->id,
            ]);

            $partyId = $employee->id;
            $linkedType = 'salary_payment';
            $linkedId = $salaryPayment->id;
        } elseif ($partyType === 'vendor') {
            $vendor = Vendor::find($data['vendor_id'] ?? null);
            if ($vendor === null) {
                throw new \InvalidArgumentException('A vendor must be selected for a Vendor Payment entry.');
            }

            $vendorPayment = $this->vendorPayableService->recordPayment($vendor, [
                'vendor_bill_id' => $data['vendor_bill_id'] ?? null,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'payment_mode' => $data['payment_mode'],
                'description' => $data['description'] ?? null,
            ], $creator);

            $partyId = $vendor->id;
            $linkedType = 'vendor_payment';
            $linkedId = $vendorPayment->id;
        } elseif ($partyType === 'client_account') {
            if (empty($data['client_account_id'])) {
                throw new \InvalidArgumentException('A customer must be selected for this entry.');
            }
            $partyId = (int) $data['client_account_id'];
        }

        $receiptPath = null;
        if (! empty($data['receipt_photo']) && $data['receipt_photo'] instanceof UploadedFile) {
            $receiptPath = $data['receipt_photo']->store('expense-receipts', 'public');
        }

        return DailyTransaction::create([
            'type' => $category->type,
            'expense_category_id' => $category->id,
            'party_type' => $partyType,
            'party_id' => $partyId,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'payment_mode' => $data['payment_mode'],
            'description' => $data['description'] ?? null,
            'receipt_photo' => $receiptPath,
            'linked_type' => $linkedType,
            'linked_id' => $linkedId,
            'created_by' => $creator->id,
        ]);
    }

    /**
     * A wrong amount entered has no in-place edit path - financial ledger
     * rows in this app are corrected by delete-and-redo, matching the
     * Invoice/Payroll/VendorBill convention elsewhere. Deleting also removes
     * the real linked SalaryPayment/VendorPayment so the worker's payroll or
     * the vendor's ledger doesn't retain a record for a transaction that no
     * longer exists here.
     */
    public function delete(int $id): void
    {
        $transaction = DailyTransaction::findOrFail($id);

        DB::transaction(function () use ($transaction) {
            $transaction->linked()?->delete();
            $transaction->delete();
        });
    }
}
