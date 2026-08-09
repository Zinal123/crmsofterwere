@extends('layouts.master')
@section('title') My Dashboard @endsection
@section('content')
<div class="row mb-3">
    <div class="col-12">
        <h4 class="fs-16 mb-1">Hi, {{ Auth::user()->name }}!</h4>
        <p class="text-muted mb-0">Here's your work for today.</p>
    </div>
</div>

<div class="row">
    <div class="col-6 col-xl-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">Assigned</p>
                <h3 class="mb-0">{{ $assignedToday }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">In Progress</p>
                <h3 class="mb-0 text-primary">{{ $inProgress }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">Completed Today</p>
                <h3 class="mb-0 text-success">{{ $completedToday }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-animate {{ $overdue > 0 ? 'border border-danger border-opacity-25' : '' }}">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">Overdue</p>
                <h3 class="mb-0 {{ $overdue > 0 ? 'text-danger' : '' }}">{{ $overdue }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">My Workload</h4></div>
            <div class="card-body">
                <div id="workerStatusChart" style="min-height: 280px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header"><h4 class="card-title mb-0">This Week</h4></div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Assigned to me</span><span class="fw-semibold">{{ $assignedToday }}</span></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">In progress</span><span class="fw-semibold text-primary">{{ $inProgress }}</span></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">On hold</span><span class="fw-semibold">{{ $onHold }}</span></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Completed today</span><span class="fw-semibold text-success">{{ $completedToday }}</span></div>
                <div class="d-flex justify-content-between py-2"><span class="text-muted">Overdue</span><span class="fw-semibold {{ $overdue > 0 ? 'text-danger' : '' }}">{{ $overdue }}</span></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h4 class="card-title mb-0 flex-grow-1">My Active Jobs</h4>
        <a href="{{ route('jobs.index') }}" class="btn btn-soft-primary btn-sm"><i class="ri-list-check align-middle me-1"></i> All my jobs</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Job</th>
                        <th>Machine</th>
                        <th>Status</th>
                        <th>Due</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeJobs as $job)
                        <tr>
                            <td><a href="{{ route('jobs.show', $job->id) }}" class="fw-medium link-primary">#{{ $job->id }} {{ $job->title ?? '' }}</a></td>
                            <td>{{ $job->machine->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary text-capitalize">{{ str_replace('_', ' ', $job->status) }}</span></td>
                            <td class="{{ $job->overdue_flagged_at ? 'text-danger fw-semibold' : '' }}">
                                {{ $job->due_date ? $job->due_date->format('d M Y') : '—' }}
                                @if($job->overdue_flagged_at) <i class="ri-alarm-warning-line"></i> @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-ui.empty-state icon="ri-briefcase-line" message="No active jobs right now. Nice work!" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    (function () {
        var el = document.querySelector('#workerStatusChart');
        var series = [{{ (int) $assignedToday }}, {{ (int) $inProgress }}, {{ (int) $onHold }}];
        if (el && typeof ApexCharts !== 'undefined' && series.some(function (n) { return n > 0; })) {
            new ApexCharts(el, {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: series,
                labels: ['Assigned', 'In Progress', 'On Hold'],
                colors: ['#0EA5E9', '#4361EE', '#F7941D'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true }
            }).render();
        } else if (el) {
            el.innerHTML = '<div class="text-center text-muted py-5"><i class="ri-briefcase-line fs-1 d-block mb-2"></i>No active jobs right now.</div>';
        }
    })();
</script>
@endsection
