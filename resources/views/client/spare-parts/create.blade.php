@extends('layouts.client')
@section('title')
Request a Spare Part
@endsection
@section('content')
<x-ui.back-link :route="route('client.dashboard')" label="Back to My Machines" />

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Request a Spare Part</h5>
        <p class="text-muted mb-3">{{ $machine->product->name ?? 'Machine' }} (SN: {{ $machine->serial_number }})</p>

        @if($spareParts->isEmpty())
            <x-ui.empty-state icon="ri-tools-fill" message="No spare parts are available to request right now. Please contact us directly." />
        @else
        <form action="{{ route('client.spare-parts.store') }}" method="POST">
            @csrf
            <input type="hidden" name="client_machine_id" value="{{ $machine->id }}">

            <div class="mb-3">
                <label class="form-label" for="spare-part-product">Part <span class="text-danger">*</span></label>
                <select id="spare-part-product" class="form-select" name="product_id" required>
                    <option value="">-- Select a part --</option>
                    @foreach($spareParts as $part)
                        <option value="{{ $part->id }}">{{ $part->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label" for="spare-part-quantity">Quantity <span class="text-danger">*</span></label>
                <input id="spare-part-quantity" type="number" class="form-control" name="quantity" value="1" min="1" max="100" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="spare-part-note">Note</label>
                <textarea id="spare-part-note" class="form-control" name="note" rows="3" placeholder="Anything else we should know?"></textarea>
            </div>

            <x-ui.button type="submit" variant="danger" icon="ri-send-plane-line">Submit Request</x-ui.button>
        </form>
        @endif
    </div>
</div>
@endsection
