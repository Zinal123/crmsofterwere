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
Invoices
@endslot
@slot('title')
Payment History
@endslot
@endcomponent
 <!-- end row-->

<div class="row">
    <div class="col-lg-12">
        <x-ui.data-table-card title="Payment History">
            <div class="table-responsive">
                <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle"
                style="width:100%">
                            <thead class="text-muted">
                                <tr>
                                    
                                    <th class="sort text-uppercase" data-sort="invoice_id">ID</th>
                                    <th class="sort text-uppercase" data-sort="customer_name">
                                        Customer</th>
                                    
                                    
                                    
                                    
                                    <th class="sort text-uppercase" data-sort="invoice_amount">
                                            Paid Amount</th>
                                    
                                    {{-- <th class="sort text-uppercase" data-sort="invoice_amount">
                                            Status</th>
                                    <th class="sort text-uppercase" data-sort="action">Action</th> --}}
                                </tr>
                            </thead>
                            <tbody class="list form-check-all" id="invoice-list-data">
                                @foreach($Paidamount as $item)
                                 <tr>
                                  <td>{{$item->id}}</td>
                                  <td>{{$item->cname}}</td>
                                  <td>{{$item->paidAmount}}</td>
                                  
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
@endsection
@section('script')
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
{{-- <script src="{{ URL::asset('build/js/pages/invoiceslist.init.js') }}"></script> --}}

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
<script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25" integrity="sha384-nLoOnA/BDh8A/jxqtckg4DumuCGOBYUnNJLZdQz/zfYNp3wcjGSoWTAzgko06G/2" crossorigin="anonymous"></script>



@endsection
