@extends('layouts.client')
@section('title')
Report a Problem
@endsection
@section('content')
<x-ui.back-link :route="route('client.dashboard')" label="Back to My Machines" />

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Report a Problem</h5>
        <p class="text-muted mb-3">{{ $machine->product->name ?? 'Machine' }} (SN: {{ $machine->serial_number }})</p>

        <form action="{{ route('client.tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="client_machine_id" value="{{ $machine->id }}">

            <div class="mb-3">
                <label class="form-label" for="ticket-category">Category <span class="text-danger">*</span></label>
                <select id="ticket-category" class="form-select">
                    <option value="">-- Select category --</option>
                    @foreach(\App\Models\TicketProblemType::CATEGORIES as $category)
                    <option value="{{ $category }}">{{ ucwords(str_replace('_', ' ', $category)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label" for="ticket-problem-type">Problem <span class="text-danger">*</span></label>
                <select id="ticket-problem-type" class="form-select" name="problem_type_id" required disabled>
                    <option value="">-- Select a category first --</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label" for="ticket-description">Description</label>
                <textarea id="ticket-description" class="form-control" name="description" rows="4" placeholder="Describe what's happening"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label" for="ticket-photos">Photos (optional, up to 5)</label>
                <input id="ticket-photos" type="file" class="form-control" name="photos[]" accept="image/*" capture="environment" multiple>
            </div>

            <x-ui.button type="submit" variant="danger" icon="ri-send-plane-line">Submit Ticket</x-ui.button>
        </form>
    </div>
</div>
@endsection
@section('script')
<script>
    var problemTypes = {!! $problemTypes->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'category' => $p->category])->values()->toJson() !!};
    var categorySelect = document.getElementById('ticket-category');
    var problemTypeSelect = document.getElementById('ticket-problem-type');

    categorySelect.addEventListener('change', function () {
        var category = this.value;
        problemTypeSelect.innerHTML = '';

        if (!category) {
            problemTypeSelect.disabled = true;
            problemTypeSelect.innerHTML = '<option value="">-- Select a category first --</option>';
            return;
        }

        var matches = problemTypes.filter(function (p) { return p.category === category; });
        problemTypeSelect.disabled = false;
        problemTypeSelect.innerHTML = '<option value="">-- Select problem --</option>' +
            matches.map(function (p) { return '<option value="' + p.id + '">' + p.name + '</option>'; }).join('');
    });
</script>
@endsection
