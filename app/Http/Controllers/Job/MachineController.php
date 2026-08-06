<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Services\Job\JobService;
use App\Services\Job\MachineService;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function __construct(
        private MachineService $service,
        private JobService $jobService,
    ) {
    }

    public function index()
    {
        return view('machines.index', ['machines' => $this->service->list()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $this->service->create($data);

        return redirect()->route('machines.index')->with('success', 'Machine added.');
    }

    public function toggle($id)
    {
        $this->service->toggleActive($id);

        return redirect()->route('machines.index')->with('success', 'Machine updated.');
    }

    public function flagDown(Request $request, $id)
    {
        $machine = Machine::find($id);
        abort_if(! $machine, 404);

        $data = $request->validate(['note' => 'nullable|string|max:1000']);

        $job = $this->jobService->flagMachineDown($machine, $request->user(), $data['note'] ?? null);

        return redirect()->route('jobs.show', $job->id)->with('success', 'Machine flagged as down. An owner has been notified.');
    }
}
