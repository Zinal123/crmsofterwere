@extends('layouts.master')
@section('title') Manager Dashboard @endsection
@section('content')

<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="mb-0">Manager Dashboard</h4></div></div></div>

<!-- Row 1: hero + operations overview -->
<div class="row">
    <div class="col-xl-4">
        <div class="card dash-card dash-hero">
            <div class="card-body">
                <h5 class="mb-1">Welcome, {{ Auth::user()->name }}! 👋</h5>
                <p class="text-muted mb-3">Shop-floor operations at a glance.</p>
                <h2 class="text-primary fw-bold tabular-nums mb-1">{{ $jobStats['completed_today'] }}</h2>
                <p class="text-muted mb-3">jobs completed today</p>
                <a href="{{ route('jobs.index') }}" class="btn btn-primary btn-sm">View Jobs</a>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card dash-card">
            <div class="card-header border-0">
                <h5 class="card-title mb-0">Operations Overview</h5>
                <p class="text-muted small mb-0">Current job pipeline &amp; support load</p>
            </div>
            <div class="card-body pt-2">
                <div class="row g-4">
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-warning-subtle text-warning"><i class="ri-time-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">Pending Approval</p>
                                <h5 class="mb-0 tabular-nums">{{ $jobStats['pending_approval'] }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-primary-subtle text-primary"><i class="ri-loader-4-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">In Progress</p>
                                <h5 class="mb-0 tabular-nums">{{ $jobStats['pending_completion'] }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-success-subtle text-success"><i class="ri-checkbox-circle-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">Completed Today</p>
                                <h5 class="mb-0 tabular-nums">{{ $jobStats['completed_today'] }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-info-subtle text-info"><i class="ri-customer-service-2-line"></i></span>
                            <div>
                                <p class="text-muted mb-0 small">Open Tickets</p>
                                <h5 class="mb-0 tabular-nums">{{ $openTickets }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Operational alerts -->
@if($overdueJobs > 0 || $lowStockCount > 0)
<div class="row">
    @if($overdueJobs > 0)
    <div class="col-xl-6">
        <a href="{{ route('jobs.index') }}" class="text-reset">
            <div class="card dash-card border border-danger border-opacity-25">
                <div class="card-body d-flex align-items-center">
                    <span class="stat-icon bg-danger-subtle text-danger me-3"><i class="ri-alarm-warning-line"></i></span>
                    <div><h4 class="mb-0 text-danger tabular-nums">{{ $overdueJobs }}</h4><p class="text-muted mb-0">Overdue job(s)</p></div>
                </div>
            </div>
        </a>
    </div>
    @endif
    @if($lowStockCount > 0)
    <div class="col-xl-6">
        <a href="{{ route('invoice.inventrylist') }}" class="text-reset">
            <div class="card dash-card border border-warning border-opacity-25">
                <div class="card-body d-flex align-items-center">
                    <span class="stat-icon bg-warning-subtle text-warning me-3"><i class="ri-stack-line"></i></span>
                    <div><h4 class="mb-0 text-warning tabular-nums">{{ $lowStockCount }}</h4><p class="text-muted mb-0">Low-stock item(s)</p></div>
                </div>
            </div>
        </a>
    </div>
    @endif
</div>
@endif

<!-- Row 2: pipeline donut + completions by worker -->
<div class="row">
    <div class="col-xl-5">
        <div class="card dash-card">
            <div class="card-header border-0"><h5 class="card-title mb-0">Job Pipeline</h5></div>
            <div class="card-body pt-0">
                <div id="jobPipelineChart" style="height: 320px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card dash-card">
            <div class="card-header border-0"><h5 class="card-title mb-0">Completions by Worker (Today)</h5></div>
            <div class="card-body pt-0">
                @php $maxWorker = collect($jobStats['by_worker'])->max() ?: 1; @endphp
                @forelse($jobStats['by_worker'] as $workerName => $count)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium">{{ $workerName ?? 'Unassigned' }}</span>
                            <span class="tabular-nums">{{ $count }}</span>
                        </div>
                        <div class="progress top-progress bg-light">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ round($count / $maxWorker * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state icon="ri-user-star-line" message="No completions logged yet today." />
                @endforelse
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
        var el = document.querySelector('#jobPipelineChart');
        var series = [{{ (int) $jobStats['pending_approval'] }}, {{ (int) $jobStats['pending_completion'] }}, {{ (int) $jobStats['completed_today'] }}];
        if (el && series.some(function (n) { return n > 0; })) {
            new ApexCharts(el, {
                chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
                series: series,
                labels: ['Pending Approval', 'In Progress', 'Completed Today'],
                colors: ['#F7941D', '#4361EE', '#10B981'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true }
            }).render();
        } else if (el) {
            el.innerHTML = '<div class="text-center text-muted py-5"><i class="ri-inbox-line fs-1 d-block mb-2"></i>No jobs in the pipeline right now.</div>';
        }
    })();
</script>
@endsection
