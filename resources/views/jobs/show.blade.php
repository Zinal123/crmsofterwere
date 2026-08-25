@extends('layouts.master')

@section('content')
@include('jobs._detail')

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Materials</h5>
                @if(count($materialAvailability['lines']))
                    @if($materialAvailability['all_available'])
                        <span class="badge bg-success-subtle text-success">Parts ready</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger">Material shortage</span>
                    @endif
                @endif
            </div>

            @if(count($materialAvailability['lines']))
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Required</th>
                                <th class="text-end">Available</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materialAvailability['lines'] as $line)
                                <tr>
                                    <td>{{ $line['product_name'] }}</td>
                                    <td class="text-end">{{ $line['required'] }}</td>
                                    <td class="text-end">{{ $line['available'] }}</td>
                                    <td>
                                        @if($line['ok'])
                                            <span class="badge bg-success-subtle text-success">Available</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Short</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @can('jobs.manage-materials')
                                            <div class="d-flex justify-content-end align-items-center gap-1">
                                                <form action="{{ route('jobs.materials.substitute', $line['material_id']) }}" method="POST" class="d-flex align-items-center gap-1">
                                                    @csrf
                                                    <label class="visually-hidden" for="substitute-part-{{ $line['material_id'] }}">Substitute part</label>
                                                    <select id="substitute-part-{{ $line['material_id'] }}" name="new_product_id" class="form-select form-select-sm" style="width: auto;" required>
                                                        <option value="">Substitute…</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <x-ui.button variant="secondary" size="sm" type="submit">Swap</x-ui.button>
                                                </form>
                                                <form action="{{ route('jobs.materials.destroy', $line['material_id']) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-ui.button variant="danger" size="sm" type="submit" icon="ri-delete-bin-fill" ariaLabel="Remove material" title="Remove material" />
                                                </form>
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small mb-0">No materials added to this job yet.</p>
            @endif

            @can('jobs.manage-materials')
                <form action="{{ route('jobs.materials.store', $job->id) }}" method="POST" class="row g-2 align-items-end mt-2">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label" for="material-product">Add part</label>
                        <select id="material-product" name="product_id" class="form-select" required>
                            <option value="">Select a part…</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="material-quantity">Quantity</label>
                        <input type="number" id="material-quantity" name="quantity" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <x-ui.button variant="secondary" type="submit" icon="ri-add-line" ariaLabel="Add material">Add Material</x-ui.button>
                    </div>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
