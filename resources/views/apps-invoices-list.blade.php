@extends('layouts.master')
@section('title')
@lang('translation.list-view')
@endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" integrity="sha384-Dv1j0mqPOKbG6R+/4/adHCn5JaMBLG3iu8uTXFBM2MjEZuKwtsyLedRcRMR0cq7P" crossorigin="anonymous" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" integrity="sha384-yeqtDRnzRLecfhe1TzrbgNOTB74vG/UQ0vsFfHPCXe5rnFfjTj3GIfBHCMlUGCh5" crossorigin="anonymous" rel="stylesheet"
    type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" integrity="sha384-/m1O+MBcLoqe7amEwkvENLD+f6ePq8wBTHBWz+Kf1zp90kmGlto3IFhhNa9fZllu" crossorigin="anonymous" rel="stylesheet" type="text/css" />
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
invoices
@endslot
@slot('title')
list view
@endslot
@endcomponent
 <!-- end row-->

<div class="row">
    <div class="col-lg-12">
        <x-ui.data-table-card title="Invoices" :create-route="route('invoice.create')" create-label="Create Invoice">
            <form method="GET" action="{{ route('invoice') }}" class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label for="from" class="form-label small mb-1">From</label>
                    <input type="date" id="from" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-auto">
                    <label for="to" class="form-label small mb-1">To</label>
                    <input type="date" id="to" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="ri-filter-3-line align-middle me-1"></i> Apply</button>
                </div>
                @if(request('from') || request('to'))
                    <div class="col-auto">
                        <a href="{{ route('invoice') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                @endif
            </form>
            <div class="table-responsive">
                <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                style="width:100%">
                            <thead class="text-muted">
                                <tr>
                                    @can('invoices.delete')
                                    <th style="width:36px;">
                                        <input type="checkbox" class="form-check-input bulk-select-all" aria-label="Select all invoices">
                                    </th>
                                    @endcan
                                    <th class="sort text-uppercase" data-sort="invoice_id">ID</th>
                                    <th class="sort text-uppercase" data-sort="customer_name">
                                        Customer</th>
                                    
                                    <th class="sort text-uppercase" data-sort="country">Phone</th>
                                    <th class="sort text-uppercase" data-sort="date">Date</th>
                                    <th class="sort text-uppercase" data-sort="invoice_amount">
                                        Amount</th>
                                    <th class="sort text-uppercase" data-sort="invoice_amount">
                                            Paid Amount</th>
                                    <th class="sort text-uppercase" data-sort="invoice_amount">
                                        Remaining Amount</th>
                                    <th class="sort text-uppercase" data-sort="invoice_amount">
                                            Status</th>
                                    <th class="sort text-uppercase" data-sort="action">Action</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all" id="invoice-list-data">
                            {{-- Skeleton placeholder rows shown only until the DataTables AJAX
                                 call (server-side, so this tbody starts empty) does its first
                                 draw - DataTables replaces the whole tbody on every draw, so
                                 these rows disappear on their own and never need JS to remove them. --}}
                            @php($invoiceColumnCount = auth()->user()->can('invoices.delete') ? 10 : 9)
                            @for ($i = 0; $i < 5; $i++)
                                <tr class="oms-skeleton-row">
                                    @for ($col = 0; $col < $invoiceColumnCount; $col++)
                                        <td><span class="oms-skeleton-bar"></span></td>
                                    @endfor
                                </tr>
                            @endfor
                            </tbody>
                </table>
            </div>

            @can('invoices.delete')
            <output class="oms-bulk-bar" id="invoices-bulk-bar" aria-live="polite">
                <span class="oms-bulk-bar-count"><span id="invoices-bulk-count">0</span> selected</span>
                <form id="invoices-bulk-delete-form" action="{{ route('invoice.bulk-delete') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm" data-confirm-delete>
                        <i class="ri-delete-bin-fill align-bottom me-1"></i> Delete Selected
                    </button>
                </form>
                <button type="button" class="btn btn-light btn-sm" id="invoices-bulk-clear">Clear</button>
            </output>
            @endcan

            <!-- Modal -->
                <div class="modal fade" id="exampleModalgrid" tabindex="-1" aria-labelledby="exampleModalgridLabel" aria-modal="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalgridLabel">Payment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="paymentForm">
                                    <input type="hidden" id="itemIdInput">
                                    <input type="hidden" id="customerInput" name="customer_id">

                                    <!-- Input to enter the amount paid -->
                                    <div class="mb-3">
                                      <label for="paidAmountInput" class="form-label">Paid Amount <span class="text-danger">*</span></label>
                                      <input type="number" class="form-control" id="paidAmountInput" name = "paidAmount" required>
                                    </div>

                                    <div class="mb-3">
                                      <label for="paymentMethodInput" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                      <select class="form-select" id="paymentMethodInput" name="payment_method" required>
                                        <option value="" selected disabled>Select payment method</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="upi">UPI</option>
                                        <option value="cheque">Cheque</option>
                                      </select>
                                    </div>

                                    <div class="mb-3">
                                      <label for="referenceNumberInput" class="form-label">Reference Number <span class="text-muted">(optional)</span></label>
                                      <input type="text" class="form-control" id="referenceNumberInput" name="reference_number" placeholder="Transaction ID, cheque no., etc.">
                                    </div>

                                    <!-- Submit button -->
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                      <button type="submit" class="btn btn-primary" id="submitPaymentBtn">Submit Payment</button>
                                    </div>
                                  </form>
                            </div>
                        </div>
                    </div>
                </div>
            <!--end modal -->
        </x-ui.data-table-card>

    </div>
    <!--end col-->
</div>
<!--end row-->
@can('invoices.delete')
    <x-ui.confirm-modal recordType="invoice" />
@endcan
@endsection
@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" integrity="sha384-ficRBwtap/VLzILv81vIvgp30PoJYnlCm96tPpNYHXAf+h9SIThOZxxIzRUzbpAh" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js" integrity="sha384-jIAE3P7Re8BgMkT0XOtfQ6lzZgbDw/02WeRMJvXK3WMHBNynEx5xofqia1OHuGh0" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js" integrity="sha384-ziUH70yXeghwn7LIJvtjobzpllxs+w4FJL4/ssbFYWoYof46CveVyQ+GCaR1eTXj" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js" integrity="sha384-WeURKcdkISkUjHZ77+LXthyEWYJMabWHrb2WEz925E9WM6I+yjuZRvUu5l21xbGT" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js" integrity="sha384-mOGjUrCoMJ8/pGqc8SQHuJdYPrdB9cjSkiuLQbw6D7orbJyMkk6xYDlYtkEH051d" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js" integrity="sha384-0RqPR2pS8PZtdf2dUWHpI+Um4DsvovzRAZPEZg7lXp4/HhxUBq0Y8VPYBE902L4u" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js" integrity="sha384-UAA3vlTPq9dwxB61awBFhR7Y5uBFOKQWuZueu4C6uI48gjIoqI/OTmYWEYWZXbGR" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js" integrity="sha384-HUHsYVOhSyHyZRTWv8zkbKVk7Xmg12CCNfKEUJ7cSuW/22Lz3BITd3Om6QeiXICb" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js" integrity="sha384-v9EFJbsxLXyYar8TvBV8zu5USBoaOC+ZB57GzCmQiWfgDIjS+wANZMP5gjwMLwGv" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25" integrity="sha384-nLoOnA/BDh8A/jxqtckg4DumuCGOBYUnNJLZdQz/zfYNp3wcjGSoWTAzgko06G/2" crossorigin="anonymous"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new DataTable('#example', {
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route("invoice.data") }}',
            data: function (d) {
                d.from = @json(request('from'));
                d.to = @json(request('to'));
            }
        },
        @php($col = auth()->user()->can('invoices.delete') ? 1 : 0)
        columns: [
            @can('invoices.delete')
            { data: 0, orderable: false, searchable: false },
            @endcan
            { data: {{ $col }}, orderable: true, searchable: true },
            { data: {{ $col + 1 }}, orderable: true, searchable: true },
            { data: {{ $col + 2 }}, orderable: true, searchable: true },
            { data: {{ $col + 3 }}, orderable: true, searchable: true },
            { data: {{ $col + 4 }}, orderable: true, searchable: true },
            { data: {{ $col + 5 }}, orderable: true, searchable: true },
            { data: {{ $col + 6 }}, orderable: true, searchable: true },
            { data: {{ $col + 7 }}, orderable: false, searchable: false },
            { data: {{ $col + 8 }}, orderable: false, searchable: false }
        ],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: 'No invoices yet.',
            zeroRecords: 'No matching invoices found.',
            search: 'Search invoices:',
        }
    });

    @can('invoices.delete')
    (function () {
        const table = document.getElementById('example');
        const bar = document.getElementById('invoices-bulk-bar');
        const countEl = document.getElementById('invoices-bulk-count');
        const form = document.getElementById('invoices-bulk-delete-form');
        const clearBtn = document.getElementById('invoices-bulk-clear');
        if (!table || !bar) {
            return;
        }

        function refresh() {
            const selected = Array.from(table.querySelectorAll('.bulk-select-row:checked'));
            countEl.textContent = selected.length;
            bar.classList.toggle('show', selected.length > 0);
            form.querySelectorAll('input[name="ids[]"]').forEach(function (el) { el.remove(); });
            selected.forEach(function (cb) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });
        }

        document.addEventListener('change', function (event) {
            if (event.target.matches('.bulk-select-row')) {
                refresh();
            }
            if (event.target.matches('.bulk-select-all')) {
                const checked = event.target.checked;
                table.querySelectorAll('.bulk-select-row').forEach(function (cb) { cb.checked = checked; });
                refresh();
            }
        });

        clearBtn.addEventListener('click', function () {
            table.querySelectorAll('.bulk-select-row:checked, .bulk-select-all:checked').forEach(function (cb) {
                cb.checked = false;
            });
            refresh();
        });

        // Server-side DataTables tears down and rebuilds every row on each
        // draw (page change, sort, search) - any selection from before that
        // draw no longer refers to what's on screen, so drop it rather than
        // let the bar/hidden inputs show stale state. DataTables fires this
        // as a jQuery event (jQuery is already loaded on this page for the
        // DataTables/Buttons plugins), not a native DOM event.
        $(table).on('draw.dt', refresh);
    })();
    @endcan
});

document.addEventListener('DOMContentLoaded', function() {
    // Delegated so it also fires for rows the DataTables AJAX call adds after page load,
    // not just rows present at DOMContentLoaded time.
    document.addEventListener('click', function(event) {
        const button = event.target.closest('.open-modal');
        if (!button) {
            return;
        }
        const itemId = button.getAttribute('data-id');
        const customer_id = button.getAttribute('data-customer');

        document.getElementById('paymentForm').reset();
        document.getElementById('itemIdInput').value = itemId;
        document.getElementById('customerInput').value = customer_id;
    });
});

    
document.getElementById('paymentForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const itemId = document.getElementById('itemIdInput').value;
        const customer_id = document.getElementById('customerInput').value
        const paidAmount = document.getElementById('paidAmountInput').value;
        const paymentMethod = document.getElementById('paymentMethodInput').value;
        const referenceNumber = document.getElementById('referenceNumberInput').value;

        // Make an AJAX request to update the payment
        fetch('/update-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for security (if using Laravel)
            },
            body: JSON.stringify({
                id: itemId,
                customer_id:customer_id,
                paidAmount: paidAmount,
                payment_method: paymentMethod,
                reference_number: referenceNumber
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show SweetAlert successss message
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Updated!',
                    text: 'The payment was updated successfully.',
                    showConfirmButton: false,
                    timer: 2000
                });

                // Hide the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('exampleModalgrid'));
                modal.hide();
                location.reload();
            } else {
                // Show SweetAlert error message. Server-side validation
                // (e.g. a payment that would exceed the remaining balance)
                // comes back as data.errors.paidAmount[0]; fall back to a
                // generic message for anything else.
                const message = (data.errors && data.errors.paidAmount && data.errors.paidAmount[0])
                    || data.message
                    || 'There was an issue updating the payment. Please try again.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: message,
                });
            }
        })
        // .catch(error => {
        //     console.error('Error:', error);

        //     // Show SweetAlert error message in case of a request failure
        //     Swal.fire({
        //         icon: 'error',
        //         title: 'Error!',
        //         text: 'An unexpected error occurred. Please try again later.',
        //     });
        // });
    });

    </script>

   
@endsection
