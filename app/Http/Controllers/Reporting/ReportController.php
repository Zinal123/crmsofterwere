<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Services\Reporting\ReportService;

class ReportController extends Controller
{
    public function __construct(private ReportService $service)
    {
    }

    public function index()
    {
        return view('reports.index', [
            'jobThroughput' => $this->service->jobThroughput(),
            'technicianPerformance' => $this->service->technicianPerformance(),
            'machineHistory' => $this->service->machineServiceHistory(),
            'inventoryLevels' => $this->service->inventoryLevels(),
        ]);
    }
}
