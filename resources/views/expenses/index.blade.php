@extends('layouts.master')
@section('title')
Daily Expenses
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Expenses
@endslot
@slot('title')
Daily Expenses
@endslot
@endcomponent

@can('expenses.manage')
<div class="d-flex justify-content-end gap-2 mb-3">
    <a href="{{ route('admin.expense-categories.index') }}" class="btn btn-soft-secondary"><i class="ri-price-tag-3-line align-middle"></i> Manage Categories</a>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
        <i class="ri-add-line align-middle"></i> Add Transaction
    </button>
</div>
@endcan

<div class="row">
    <div class="col-lg-12">
        <x-ui.data-table-card title="Recent Transactions" :createRoute="route('expenses.cashbook')" createLabel="Cash Book">
            <form method="GET" action="{{ route('expenses.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label for="filter-from" class="form-label small mb-1">From</label>
                    <input type="date" id="filter-from" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-auto">
                    <label for="filter-to" class="form-label small mb-1">To</label>
                    <input type="date" id="filter-to" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-auto">
                    <label for="filter-type" class="form-label small mb-1">Type</label>
                    <select id="filter-type" name="type" class="form-select">
                        <option value="">All</option>
                        <option value="payment" @selected(request('type') === 'payment')>Money Out</option>
                        <option value="receipt" @selected(request('type') === 'receipt')>Money In</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="ri-filter-3-line align-middle me-1"></i> Apply</button>
                </div>
                @if(request('from') || request('to') || request('type'))
                    <div class="col-auto">
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                @endif
            </form>
            @if(request('exclude_mirrors'))
                <p class="text-muted small"><i class="ri-information-line align-middle"></i> Wage and vendor payments already counted from payroll/vendor records are hidden here to match the report total.</p>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Date</th><th>Type</th><th>Category</th><th>Amount</th><th>Mode</th><th>Description</th>@can('expenses.delete')<th></th>@endcan</tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->date->format('d M Y') }}</td>
                            <td>
                                @if($transaction->type === 'payment')
                                    <x-ui.status-badge status="Money Out" variant="danger" icon="ri-arrow-up-circle-line" />
                                @else
                                    <x-ui.status-badge status="Money In" variant="success" icon="ri-arrow-down-circle-line" />
                                @endif
                            </td>
                            <td>{{ $transaction->category->name ?? '—' }}</td>
                            <td>{{ \App\Support\IndianNumber::format($transaction->amount) }}</td>
                            <td>{{ ucfirst($transaction->payment_mode) }}</td>
                            <td>{{ $transaction->description ?: '—' }}</td>
                            @can('expenses.delete')
                            <td>
                                <form action="{{ route('expenses.destroy', $transaction->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-sm" data-confirm-delete title="Delete" aria-label="Delete">
                                        <i class="ri-delete-bin-fill align-bottom"></i>
                                    </button>
                                </form>
                            </td>
                            @endcan
                        </tr>
                        @empty
                        <tr><td colspan="7"><x-ui.empty-state icon="ri-exchange-dollar-line" message="No transactions recorded yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>
    </div>
</div>

@can('expenses.manage')
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" id="transaction-form">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="type-payment" value="payment" checked>
                            <label class="btn btn-outline-danger" for="type-payment">Money Out</label>
                            <input type="radio" class="btn-check" name="type" id="type-receipt" value="receipt">
                            <label class="btn btn-outline-success" for="type-receipt">Money In</label>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="transaction-category">Category <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            <select id="transaction-category" class="form-select" name="expense_category_id" required>
                                <optgroup label="Payment" id="payment-options">
                                    @foreach($paymentCategories as $category)
                                        <option value="{{ $category->id }}" data-party="{{ $category->party_model }}">{{ $category->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Receipt" id="receipt-options" style="display: none;">
                                    @foreach($receiptCategories as $category)
                                        <option value="{{ $category->id }}" data-party="{{ $category->party_model }}">{{ $category->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <button type="button" class="btn btn-soft-secondary" data-bs-toggle="modal" data-bs-target="#quickAddCategoryModal" title="Add a new category" aria-label="Add a new category">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-2" id="employee-field" style="display: none;">
                        <label class="form-label" for="transaction-employee">Worker <span class="text-danger">*</span></label>
                        <select id="transaction-employee" class="form-select" name="employee_id">
                            <option value="">-- Select a worker --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-text mb-0">This posts a real payment against this worker's salary — it'll show on their Payroll page too.</p>
                    </div>

                    <div class="mb-2" id="vendor-field" style="display: none;">
                        <label class="form-label" for="transaction-vendor">Vendor <span class="text-danger">*</span></label>
                        <select id="transaction-vendor" class="form-select" name="vendor_id">
                            <option value="">-- Select a vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-text mb-0">This posts against the vendor's payable ledger. Pick the specific bill on the vendor's page for finer control, or record it here as a general payment.</p>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="transaction-amount">Amount <span class="text-danger">*</span></label>
                        <input id="transaction-amount" type="number" step="0.01" min="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="transaction-date">Date <span class="text-danger">*</span></label>
                        <input id="transaction-date" type="date" class="form-control" name="date" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="transaction-mode">Payment Mode <span class="text-danger">*</span></label>
                        <select id="transaction-mode" class="form-select" name="payment_mode" required>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="transaction-description">Description</label>
                        <textarea id="transaction-description" class="form-control" name="description"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="transaction-receipt">Receipt Photo (optional)</label>
                        <input id="transaction-receipt" type="file" class="form-control" name="receipt_photo" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <x-ui.button variant="secondary" type="button" data-bs-dismiss="modal">Cancel</x-ui.button>
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Add Transaction</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@can('expenses.manage')
<div class="modal fade" id="quickAddCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add a New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label" for="quick-category-name">Name <span class="text-danger">*</span></label>
                    <input id="quick-category-name" type="text" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label" for="quick-category-type">Applies To <span class="text-danger">*</span></label>
                    <select id="quick-category-type" class="form-select">
                        <option value="payment">Payment (money out)</option>
                        <option value="receipt">Receipt (money in)</option>
                    </select>
                </div>
                <div id="quick-category-error" class="text-danger small" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <x-ui.button variant="secondary" type="button" data-bs-dismiss="modal">Cancel</x-ui.button>
                <x-ui.button variant="primary" type="button" id="quick-category-save">Add</x-ui.button>
            </div>
        </div>
    </div>
</div>
@endcan

@can('expenses.delete')
    <x-ui.confirm-modal recordType="transaction" />
@endcan
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var typeRadios = document.querySelectorAll('input[name="type"]');
    var paymentGroup = document.getElementById('payment-options');
    var receiptGroup = document.getElementById('receipt-options');
    var categorySelect = document.getElementById('transaction-category');
    var employeeField = document.getElementById('employee-field');
    var vendorField = document.getElementById('vendor-field');

    function syncCategoryOptionsForType() {
        var isPayment = document.getElementById('type-payment').checked;
        paymentGroup.style.display = isPayment ? '' : 'none';
        receiptGroup.style.display = isPayment ? 'none' : '';
        Array.from(paymentGroup.querySelectorAll('option')).forEach(function (opt) { opt.disabled = !isPayment; });
        Array.from(receiptGroup.querySelectorAll('option')).forEach(function (opt) { opt.disabled = isPayment; });
        categorySelect.value = '';
        syncPartyFields();
    }

    function syncPartyFields() {
        var selected = categorySelect.options[categorySelect.selectedIndex];
        var party = selected ? selected.dataset.party : '';
        employeeField.style.display = party === 'employee' ? 'block' : 'none';
        vendorField.style.display = party === 'vendor' ? 'block' : 'none';
        document.getElementById('transaction-employee').required = party === 'employee';
        document.getElementById('transaction-vendor').required = party === 'vendor';
    }

    typeRadios.forEach(function (radio) { radio.addEventListener('change', syncCategoryOptionsForType); });
    categorySelect.addEventListener('change', syncPartyFields);
    syncCategoryOptionsForType();

    var quickAddBtn = document.getElementById('quick-category-save');
    if (quickAddBtn) {
        quickAddBtn.addEventListener('click', function () {
            var name = document.getElementById('quick-category-name').value;
            var type = document.getElementById('quick-category-type').value;
            var errorEl = document.getElementById('quick-category-error');
            errorEl.style.display = 'none';

            fetch('{{ route('admin.expense-categories.quick-add') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name: name, type: type, party_model: '' }),
            }).then(function (response) {
                if (!response.ok) {
                    return response.json().then(function (data) { throw data; });
                }
                return response.json();
            }).then(function (data) {
                var option = document.createElement('option');
                option.value = data.category.id;
                option.textContent = data.category.name;
                option.dataset.party = '';
                (type === 'payment' ? paymentGroup : receiptGroup).appendChild(option);

                document.getElementById('type-' + type).checked = true;
                syncCategoryOptionsForType();
                categorySelect.value = data.category.id;
                syncPartyFields();

                bootstrap.Modal.getInstance(document.getElementById('quickAddCategoryModal')).hide();
                document.getElementById('quick-category-name').value = '';
            }).catch(function () {
                errorEl.textContent = 'Could not add category — check the name and try again.';
                errorEl.style.display = 'block';
            });
        });
    }
});
</script>
@endsection
