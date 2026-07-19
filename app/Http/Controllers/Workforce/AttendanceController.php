<?php

namespace App\Http\Controllers\Workforce;

use App\Http\Controllers\Controller;
use App\Services\Workforce\AttendanceService;
use App\Services\Workforce\EmployeeService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $service,
        private EmployeeService $employeeService,
    ) {
    }

    public function mark(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $employees = $this->employeeService->activeList();
        $existing = $this->service->forDate($date)->keyBy('employee_id');

        return view('attendance.mark', compact('date', 'employees', 'existing'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'rows' => 'required|array|min:1',
            'rows.*.employee_id' => 'required|exists:employees,id',
            'rows.*.status' => 'required|in:present,absent,half_day,leave',
            'rows.*.overtime_hours' => 'nullable|numeric|min:0',
        ]);

        $this->service->markForDate($data['date'], $data['rows'], $request->user());

        return redirect()->route('attendance.mark', ['date' => $data['date']])->with('success', 'Attendance saved.');
    }

    public function register(Request $request, $employee)
    {
        $employeeModel = $this->employeeService->find($employee);
        abort_if(! $employeeModel, 404);

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $attendanceRows = $this->service->forEmployeeAndMonth((int) $employee, $year, $month);

        return view('attendance.register', ['employee' => $employeeModel, 'attendanceRows' => $attendanceRows, 'year' => $year, 'month' => $month]);
    }
}
