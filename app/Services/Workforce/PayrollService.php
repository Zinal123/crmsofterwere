<?php

namespace App\Services\Workforce;

use App\Models\Employee;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\SalaryPaymentRepositoryInterface;
use Carbon\Carbon;

class PayrollService
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepository,
        private SalaryPaymentRepositoryInterface $salaryPaymentRepository,
    ) {
    }

    public function calculateMonthlyEarnings(Employee $employee, int $year, int $month): array
    {
        $attendanceRows = $this->attendanceRepository->forEmployeeAndMonth($employee->id, $year, $month);

        $daysPresent = $attendanceRows->where('status', 'present')->count();
        $daysHalf = $attendanceRows->where('status', 'half_day')->count();
        $daysLeave = $attendanceRows->where('status', 'leave')->count();
        $daysAbsent = $attendanceRows->where('status', 'absent')->count();
        $overtimeHours = (float) $attendanceRows->sum('overtime_hours');

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $dayRate = $employee->pay_type === 'daily'
            ? (float) $employee->pay_rate
            : (float) $employee->pay_rate / $daysInMonth;

        $baseEarned = ($daysPresent * $dayRate) + ($daysHalf * $dayRate * 0.5);
        $overtimeEarned = $overtimeHours * (float) $employee->overtime_rate_per_hour;
        $totalEarned = $baseEarned + $overtimeEarned;

        $totalPaid = $this->salaryPaymentRepository->totalForEmployeeAndMonth($employee->id, $year, $month);

        return [
            'days_present' => $daysPresent,
            'days_half' => $daysHalf,
            'days_leave' => $daysLeave,
            'days_absent' => $daysAbsent,
            'day_rate' => round($dayRate, 2),
            'base_earned' => round($baseEarned, 2),
            'overtime_hours' => $overtimeHours,
            'overtime_earned' => round($overtimeEarned, 2),
            'total_earned' => round($totalEarned, 2),
            'total_paid' => round($totalPaid, 2),
            'balance_due' => round($totalEarned - $totalPaid, 2),
        ];
    }
}
