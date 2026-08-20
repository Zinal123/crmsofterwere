@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.back-link :route="route('root')" label="Back to Dashboard" />
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Mark Attendance</h5>
                <form method="GET" action="{{ route('attendance.mark') }}" class="d-flex gap-2">
                    <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
                </form>
            </div>

            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Overtime Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $index => $employee)
                            @php $existingRow = $existing->get($employee->id); @endphp
                            <tr>
                                <td>
                                    @can('attendance.view')
                                        <a href="{{ route('attendance.register', ['employee' => $employee->id, 'year' => \Illuminate\Support\Carbon::parse($date)->year, 'month' => \Illuminate\Support\Carbon::parse($date)->month]) }}">{{ $employee->name }}</a>
                                    @else
                                        {{ $employee->name }}
                                    @endcan
                                    <input type="hidden" name="rows[{{ $index }}][employee_id]" value="{{ $employee->id }}">
                                </td>
                                <td>
                                    <select name="rows[{{ $index }}][status]" class="form-select">
                                        <option value="present" @selected(($existingRow->status ?? 'present') === 'present')>Present</option>
                                        <option value="absent" @selected(($existingRow->status ?? '') === 'absent')>Absent</option>
                                        <option value="half_day" @selected(($existingRow->status ?? '') === 'half_day')>Half Day</option>
                                        <option value="leave" @selected(($existingRow->status ?? '') === 'leave')>Leave</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.5" min="0" name="rows[{{ $index }}][overtime_hours]" class="form-control" value="{{ $existingRow->overtime_hours ?? 0 }}">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-ui.empty-state icon="ri-team-line" message="No active employees to mark." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                @if($employees->isNotEmpty())
                    <x-ui.button variant="success" type="submit" icon="ri-checkbox-circle-line" ariaLabel="Save attendance">Save Attendance</x-ui.button>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
