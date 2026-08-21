@extends('layouts.master')
@section('title')
Expense Categories
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Expenses
@endslot
@slot('title')
Categories
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Add Category</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.expense-categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label" for="category-name">Name <span class="text-danger">*</span></label>
                        <input id="category-name" type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="category-type">Applies To <span class="text-danger">*</span></label>
                        <select id="category-type" class="form-select" name="type" required>
                            <option value="payment">Payment (money out)</option>
                            <option value="receipt">Receipt (money in)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="category-party">Requires Linking To</label>
                        <select id="category-party" class="form-select" name="party_model">
                            <option value="">Nothing (standalone expense)</option>
                            <option value="employee">A Worker (posts to their salary)</option>
                            <option value="vendor">A Vendor (posts to their payable ledger)</option>
                            <option value="client_account">A Customer</option>
                        </select>
                    </div>
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Add Category</x-ui.button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <x-ui.data-table-card title="Expense Categories">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Name</th><th>Applies To</th><th>Links To</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->type === 'payment' ? 'Payment' : 'Receipt' }}</td>
                            <td>{{ $category->party_model ? ucfirst(str_replace('_', ' ', $category->party_model)) : '—' }}</td>
                            <td>
                                <form action="{{ route('admin.expense-categories.toggle', $category->id) }}" method="POST">
                                    @csrf
                                    <x-ui.toggle :checked="$category->is_active" name="is_active" :ariaLabel="$category->name . ' active status'" />
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><x-ui.empty-state icon="ri-price-tag-3-line" message="No categories yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>
@endsection
