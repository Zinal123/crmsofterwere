<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Services\Job\MachineService;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function __construct(private MachineService $service)
    {
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
}
