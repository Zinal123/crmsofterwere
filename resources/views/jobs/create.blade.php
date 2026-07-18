@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $canAssign ? 'Request or Assign a Job' : 'Request a Job' }}</h5>
            <form action="{{ route('jobs.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="job-title">Title</label>
                    <input id="job-title" type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-description">Description</label>
                    <textarea id="job-description" name="description" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-machine">Machine (in-house)</label>
                    <select id="job-machine" name="machine_id" class="form-select">
                        <option value="">-- Outside site instead --</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-site">Outside Site Name</label>
                    <input id="job-site" type="text" name="site_name" class="form-control" placeholder="Only if not an in-house machine">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-priority">Priority</label>
                    <select id="job-priority" name="priority" class="form-select">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-due-date">Due Date (optional)</label>
                    <input id="job-due-date" type="date" name="due_date" class="form-control">
                </div>
                @if($canAssign)
                    <div class="mb-3">
                        <label class="form-label" for="job-assign">Assign to Worker (skips approval)</label>
                        <select id="job-assign" name="assigned_to" class="form-select">
                            <option value="">-- Leave blank to submit as your own request --</option>
                            @foreach(\App\Models\User::role('Worker')->get() as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <x-ui.button variant="success" type="submit" icon="ri-send-plane-line" ariaLabel="Submit job">Submit</x-ui.button>
            </form>
        </div>
    </div>
</div>
@endsection
