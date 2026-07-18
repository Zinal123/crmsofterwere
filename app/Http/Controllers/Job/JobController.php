<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Services\Job\JobService;
use App\Services\Job\MachineService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct(
        private JobService $service,
        private MachineService $machineService,
        private JobRepositoryInterface $repository,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $jobs = $user->can('jobs.view-all') ? $this->repository->all() : $this->repository->allForUser($user);

        return view('jobs.index', compact('jobs'));
    }

    public function create(Request $request)
    {
        return view('jobs.create', [
            'machines' => $this->machineService->list(),
            'canAssign' => $request->user()->can('jobs.assign'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'machine_id' => 'nullable|exists:machines,id',
            'site_name' => 'nullable|string|max:255',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        try {
            if ($request->user()->can('jobs.assign') && ! empty($data['assigned_to'])) {
                $job = $this->service->createAssigned($data, $request->user());
            } else {
                $job = $this->service->createRequest($data, $request->user());
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job created.');
    }

    public function show(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);
        abort_unless(
            $request->user()->can('jobs.view-all') || $job->created_by === $request->user()->id || $job->assigned_to === $request->user()->id,
            403
        );

        return view('jobs.show', compact('job'));
    }

    public function approve(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->approve($job, $request->user(), $request->input('assigned_to'));
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.pending-approval')->with('success', 'Job approved.');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->reject($job, $request->user(), $data['rejection_reason']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.pending-approval')->with('success', 'Job rejected.');
    }

    public function pendingApproval()
    {
        return view('jobs.pending-approval', ['jobs' => $this->repository->pendingApproval()]);
    }

    public function start(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->start($job, $request->user());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job started.');
    }

    public function hold(Request $request, $id)
    {
        $data = $request->validate(['on_hold_reason' => 'required|string|max:1000']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->hold($job, $request->user(), $data['on_hold_reason']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job put on hold.');
    }

    public function resume(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->resume($job, $request->user());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job resumed.');
    }
}
