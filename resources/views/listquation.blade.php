@extends('layouts.master')
@section('title')
@lang('Qutation')
@endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<!--datatable css-->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" integrity="sha384-Dv1j0mqPOKbG6R+/4/adHCn5JaMBLG3iu8uTXFBM2MjEZuKwtsyLedRcRMR0cq7P" crossorigin="anonymous" rel="stylesheet" type="text/css" />
<!--datatable responsive css-->
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" integrity="sha384-yeqtDRnzRLecfhe1TzrbgNOTB74vG/UQ0vsFfHPCXe5rnFfjTj3GIfBHCMlUGCh5" crossorigin="anonymous" rel="stylesheet"type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" integrity="sha384-/m1O+MBcLoqe7amEwkvENLD+f6ePq8wBTHBWz+Kf1zp90kmGlto3IFhhNa9fZllu" crossorigin="anonymous" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/dropzone/dropzone.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ URL::asset('build/libs/filepond/filepond.min.css') }}" type="text/css" />
<link rel="stylesheet"href="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
     
@endslot
@slot('title')

@endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">
        <x-ui.data-table-card title="Quotations" :create-route="route('generatequtation', 1)" create-label="Create Quotation">
            <div class="table-responsive">
                <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                    style="width:100%">
                    <thead>
                        <tr>
                            
                            <th data-ordering="true"style = "width:5%;">SR No.</th>
                            <th data-ordering="true" style = "width:230px;">Name</th>
                            <th data-ordering="true">Email</th>
                            <th data-ordering="true">Phone</th>

                            <th style = "width:10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product as $item)
                        <tr>
                            
                            <td>{{$item->id}}</td>
                            <td>{{$item->clientname}}</td>

                            <td>{{$item->email}}</td>
                            <td>{{$item->phone}}</td>
                            <td>
                               <div class="d-flex gap-2 flex-wrap">
                                   @can('quotations.update')
                                   <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editQuotation-{{ $item->id }}" title="Edit" aria-label="Edit">
                                     <i class="ri-edit-line align-bottom"></i>
                                   </button>
                                   @endcan
                                   @can('quotations.update')
                                       @can('invoices.create')
                                           @if($item->invoice_id)
                                               <a href="{{ route('invoice.details', $item->invoice_id) }}" class="btn btn-soft-info btn-sm" data-bs-toggle="tooltip" title="View Invoice" aria-label="View Invoice"><i class="ri-file-list-3-line align-bottom"></i></a>
                                           @else
                                               <button type="button" class="btn btn-soft-warning btn-sm" data-bs-toggle="modal" data-bs-target="#convertQuotation-{{ $item->id }}" title="Convert to Invoice" aria-label="Convert to Invoice">
                                                   <i class="ri-file-transfer-line align-bottom"></i>
                                               </button>
                                           @endif
                                       @endcan
                                   @endcan
                                   <a href="{{route('quation.pdf' ,$item->id)}}" class="btn btn-soft-success btn-sm" data-bs-toggle="tooltip" title="Download Qutation" aria-label="Download Qutation"><i class="ri-download-2-line align-bottom"></i></a>
                                   <a href="{{route('quation.print' ,$item->id)}}" target="_blank" rel="noopener" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" title="Print Quotation" aria-label="Print Quotation"><i class="ri-printer-line align-bottom"></i></a>
                                   @can('quotations.delete')
                                   <a href="{{route('quation.delete', $item->id)}}" class="btn btn-soft-danger btn-sm" data-confirm-delete data-bs-toggle="tooltip" title="Delete" aria-label="Delete">
                                     <i class="ri-delete-bin-fill align-bottom"></i>
                                   </a>
                                   @endcan
                                   @can('quotations.view-audit')
                                   <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#auditTrailModal-quotation" data-audit-id="{{$item->id}}" title="History" aria-label="History">
                                     <i class="ri-history-line align-bottom"></i>
                                   </button>
                                   @endcan
                               </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.data-table-card>

    </div>
    <!--end col-->
</div>
<!--end row-->

@can('quotations.update')
@can('invoices.create')
@foreach($product as $item)
    @if(!$item->invoice_id)
    <div class="modal fade" id="convertQuotation-{{ $item->id }}" tabindex="-1" aria-labelledby="convertQuotation-{{ $item->id }}-label" aria-modal="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="convertQuotation-{{ $item->id }}-label">Convert to Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('quation.convert-to-invoice', $item->id) }}" method="POST">
                        @csrf
                        <p class="text-muted small">Creates a real invoice from this quotation's client info and pricing lines. GST rates default to 0 &mdash; review them on the new invoice before finalizing.</p>
                        <div class="mb-3">
                            <label class="form-label" for="convert-state-{{ $item->id }}">Customer's State <span class="text-danger">*</span></label>
                            <select class="form-select" id="convert-state-{{ $item->id }}" name="state" required>
                                <option value="">-- Select state --</option>
                                @foreach(\App\Support\IndianStates::LIST as $stateOption)
                                    <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Needed for GST calculation &mdash; not captured on the quotation form itself.</div>
                        </div>
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">Convert to Invoice</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endcan
@endcan

@can('quotations.update')
@foreach($product as $item)
<div class="modal fade" id="editQuotation-{{ $item->id }}" tabindex="-1" aria-labelledby="editQuotation-{{ $item->id }}-label" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editQuotation-{{ $item->id }}-label">Edit Quotation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('quation.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-quotation-clientname-{{ $item->id }}">Client Name</label>
                            <input type="text" class="form-control" id="edit-quotation-clientname-{{ $item->id }}" name="clientname" value="{{ $item->clientname }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-quotation-companyname-{{ $item->id }}">Company Name</label>
                            <input type="text" class="form-control" id="edit-quotation-companyname-{{ $item->id }}" name="companyname" value="{{ $item->companyname }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-quotation-gstno-{{ $item->id }}">GST No.</label>
                            <input type="text" class="form-control" id="edit-quotation-gstno-{{ $item->id }}" name="gstno" value="{{ $item->gstno }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-quotation-companyaddress-{{ $item->id }}">Company Address</label>
                            <input type="text" class="form-control" id="edit-quotation-companyaddress-{{ $item->id }}" name="companyaddress" value="{{ $item->companyaddress }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-quotation-email-{{ $item->id }}">Email</label>
                            <input type="email" class="form-control" id="edit-quotation-email-{{ $item->id }}" name="email" value="{{ $item->email }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label" for="edit-quotation-phone-{{ $item->id }}">Phone</label>
                            <input type="text" class="form-control" id="edit-quotation-phone-{{ $item->id }}" name="phone" value="{{ $item->phone }}">
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label" for="edit-quotation-note-{{ $item->id }}">Note</label>
                            <textarea class="form-control" id="edit-quotation-note-{{ $item->id }}" name="note" rows="2">{{ $item->note }}</textarea>
                        </div>
                    </div>
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endcan

<x-ui.confirm-modal recordType="quotation" />
@endsection
@section('script')
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/invoiceslist.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" integrity="sha384-ficRBwtap/VLzILv81vIvgp30PoJYnlCm96tPpNYHXAf+h9SIThOZxxIzRUzbpAh" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js" integrity="sha384-jIAE3P7Re8BgMkT0XOtfQ6lzZgbDw/02WeRMJvXK3WMHBNynEx5xofqia1OHuGh0" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js" integrity="sha384-ziUH70yXeghwn7LIJvtjobzpllxs+w4FJL4/ssbFYWoYof46CveVyQ+GCaR1eTXj" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js" integrity="sha384-WeURKcdkISkUjHZ77+LXthyEWYJMabWHrb2WEz925E9WM6I+yjuZRvUu5l21xbGT" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js" integrity="sha384-mOGjUrCoMJ8/pGqc8SQHuJdYPrdB9cjSkiuLQbw6D7orbJyMkk6xYDlYtkEH051d" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js" integrity="sha384-0RqPR2pS8PZtdf2dUWHpI+Um4DsvovzRAZPEZg7lXp4/HhxUBq0Y8VPYBE902L4u" crossorigin="anonymous"></script>
<script src="{{ URL::asset('build/libs/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}">
    </script>
    <script
        src="{{ URL::asset('build/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}">
    </script>
    <script
        src="{{ URL::asset('build/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js') }}"></script>

    <script src="{{ URL::asset('build/js/pages/form-file-upload.init.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs" crossorigin="anonymous"></script>




@can('quotations.view-audit')
    <x-ui.audit-trail-modal type="quotation" />
@endcan

@endsection
