@extends('layouts.master')
@section('title') Manager Dashboard @endsection
@section('content')
<div class="row mb-3">
    <div class="col-12">
        <h4 class="fs-16 mb-1">Welcome, {{ Auth::user()->name }}!</h4>
        <p class="text-muted mb-0">Shop-floor operations at a glance.</p>
    </div>
</div>

<div class="row">
    <div class="col-6 col-xl-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">Pending Approval</p>
                <h3 class="mb-0">{{ $jobStats['pending_approval'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">In Progress</p>
                <h3 class="mb-0 text-primary">{{ $jobStats['pending_completion'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">Completed Today</p>
                <h3 class="mb-0 text-success">{{ $jobStats['completed_today'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-2">Open Tickets</p>
                <h3 class="mb-0">{{ $openTickets }}</h3>
            </div>
        </div>
    </div>
</div>

@if($overdueJobs > 0 || $lowStockCount > 0)
<div class="row">
    @if($overdueJobs > 0)
    <div class="col-xl-6">
        <a href="{{ route('jobs.index') }}" class="text-reset">
            <div class="card border border-danger border-opacity-25">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0 me-3"><span class="avatar-title rounded bg-danger-subtle text-danger fs-3"><i class="ri-alarm-warning-line"></i></span></div>
                    <div><h4 class="mb-0 text-danger">{{ $overdueJobs }}</h4><p class="text-muted mb-0">Overdue job(s)</p></div>
                </div>
            </div>
        </a>
    </div>
    @endif
    @if($lowStockCount > 0)
    <div class="col-xl-6">
        <a href="{{ route('invoice.inventrylist') }}" class="text-reset">
            <div class="card border border-warning border-opacity-25">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0 me-3"><span class="avatar-title rounded bg-warning-subtle text-warning fs-3"><i class="ri-stack-line"></i></span></div>
                    <div><h4 class="mb-0 text-warning">{{ $lowStockCount }}</h4><p class="text-muted mb-0">Low-stock item(s)</p></div>
                </div>
            </div>
        </a>
    </div>
    @endif
</div>
@endif

<div class="row">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Job Pipeline</h4></div>
            <div class="card-body">
                <div id="jobPipelineChart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Completions by Worker (Today)</h4></div>
            <div class="card-body">
                @forelse($jobStats['by_worker'] as $workerName => $count)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>{{ $workerName ?? 'Unassigned' }}</span>
                        <span class="fw-semibold">{{ $count }}</span>
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
        var el = document.querySelector('#jobPipelineChart');
        var series = [{{ (int) $jobStats['pending_approval'] }}, {{ (int) $jobStats['pending_completion'] }}, {{ (int) $jobStats['completed_today'] }}];
        if (el && typeof ApexCharts !== 'undefined' && series.some(function (n) { return n > 0; })) {
            new ApexCharts(el, {
                chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
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
