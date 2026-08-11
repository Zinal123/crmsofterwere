@extends('layouts.master')
@section('title') My Dashboard @endsection
@section('content')

<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="mb-0">My Dashboard</h4></div></div></div>

<!-- Row 1: hero + my work overview -->
<div class="row">
    <div class="col-xl-4">
        <div class="card dash-card dash-hero">
            <div class="card-body">
                <h5 class="mb-1">Hi, {{ Auth::user()->name }}! 👋</h5>
                <p class="text-muted mb-3">Here's your work for today.</p>
                <h2 class="text-primary fw-bold tabular-nums mb-1">{{ $inProgress }}</h2>
                <p class="text-muted mb-3">job(s) in progress</p>
                <a href="{{ route('jobs.index') }}" class="btn btn-primary btn-sm">All my jobs</a>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card dash-card">
            <div class="card-header border-0">
                <h5 class="card-title mb-0">My Work Overview</h5>
                <p class="text-muted small mb-0">Your current job load</p>
            </div>
            <div class="card-body pt-2">
                <div class="row g-4">
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-info-subtle text-info"><i class="ri-inbox-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">Assigned</p>
                                <h5 class="mb-0 tabular-nums">{{ $assignedToday }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-primary-subtle text-primary"><i class="ri-loader-4-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">In Progress</p>
                                <h5 class="mb-0 tabular-nums">{{ $inProgress }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-success-subtle text-success"><i class="ri-checkbox-circle-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">Completed Today</p>
                                <h5 class="mb-0 tabular-nums">{{ $completedToday }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-danger-subtle text-danger"><i class="ri-alarm-warning-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">Overdue</p>
                                <h5 class="mb-0 tabular-nums {{ $overdue > 0 ? 'text-danger' : '' }}">{{ $overdue }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: workload donut + active jobs -->
<div class="row">
    <div class="col-xl-4">
        <div class="card dash-card">
            <div class="card-header border-0"><h5 class="card-title mb-0">My Workload</h5></div>
            <div class="card-body pt-0">
                <div id="workerStatusChart" style="height: 320px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card dash-card">
            <div class="card-header border-0 d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">My Active Jobs</h5>
                <a href="{{ route('jobs.index') }}" class="btn btn-soft-primary btn-sm"><i class="ri-list-check align-middle me-1"></i> All my jobs</a>
            </div>
            <div class="card-body pt-0">
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
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    (function () {
        if (typeof ApexCharts === 'undefined') return;
        var el = document.querySelector('#workerStatusChart');
        var series = [{{ (int) $assignedToday }}, {{ (int) $inProgress }}, {{ (int) $onHold }}];
        if (el && series.some(function (n) { return n > 0; })) {
            new ApexCharts(el, {
                chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
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
