<?php

namespace App\Http\Controllers\Workforce;

use App\Http\Controllers\Controller;
use App\Services\Workforce\EmployeeService;
use App\Services\Workforce\PayrollService;
use App\Services\Workforce\SalaryPaymentService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService,
        private PayrollService $payrollService,
        private SalaryPaymentService $paymentService,
    ) {
    }

    public function index(Request $request)
    {
        return view('payroll.index', [
            'payments' => $this->paymentService->forDateRange($request->input('from'), $request->input('to')),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ]);
    }

    public function show(Request $request, $employee)
    {
        $employeeModel = $this->employeeService->find($employee);
        abort_if(! $employeeModel, 404);

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $earnings = $this->payrollService->calculateMonthlyEarnings($employeeModel, $year, $month);
        $payments = $this->paymentService->forEmployeeAndMonth((int) $employee, $year, $month);

        return view('payroll.show', ['employee' => $employeeModel, 'earnings' => $earnings, 'payments' => $payments, 'year' => $year, 'month' => $month]);
    }

    public function storePayment(Request $request, $employee)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);
        $employeeModel = $this->employeeService->find($employee);
        abort_if(! $employeeModel, 404);

        $this->paymentService->create([
            'employee_id' => $employeeModel->id,
            'date' => $data['date'],
            'amount' => $data['amount'],
            'note' => $data['note'] ?? null,
            'paid_by' => $request->user()->id,
        ]);

        return redirect()->route('employees.payroll', ['employee' => $employeeModel->id, 'year' => date('Y', strtotime($data['date'])), 'month' => date('n', strtotime($data['date']))])->with('success', 'Payment recorded.');
    }

    public function updatePayment(Request $request, $employee, $paymentId)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        $this->paymentService->update($paymentId, $data);

        return redirect()->route('employees.payroll', ['employee' => $employee, 'year' => date('Y', strtotime($data['date'])), 'month' => date('n', strtotime($data['date']))])->with('success', 'Payment updated.');
    }

    public function destroyPayment(Request $request, $employee, $paymentId)
    {
        $this->paymentService->delete($paymentId);

        return redirect()->route('employees.payroll', $employee)->with('success', 'Payment deleted.');
    }
}
