@extends('layouts.master')
@section('title') Quotation @endsection
@section('css')
<style>

   .table1{
    margin-bottom: 0rem !important;
    padding: 0rem !important;
   }
</style>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Quation @endslot
@slot('title') Quation Details @endslot
@endcomponent

@php
    $isCo2 = (int) $quotation->product_id === 2;
    $productLabel = $isCo2 ? 'CO2 Laser Cutting Machine' : 'Fiber Laser Cutting Machine';
    $recipientName = $quotation->companyname ?: $quotation->clientname;

    $configRows = collect([
        ['label' => 'Software', 'item' => $software, 'nameField' => 'modal'],
        ['label' => 'Laser Cutting Machine', 'item' => $lasercuttingMachine, 'nameField' => 'modal'],
        ['label' => 'Focusing Laser Cutting Head', 'item' => $focus, 'nameField' => 'modal'],
        ['label' => 'Power Source', 'item' => $power, 'nameField' => 'modal'],
        ['label' => 'Motor', 'item' => $motor, 'nameField' => 'companyname'],
        ['label' => 'Motor Type', 'item' => $motorType, 'nameField' => 'companyname'],
        ['label' => 'Gear Box', 'item' => $gear, 'nameField' => 'companyname'],
        ['label' => 'Rack', 'item' => $rack, 'nameField' => 'companyname'],
        ['label' => 'Software (Accessory)', 'item' => $software1, 'nameField' => 'companyname'],
    ])->filter(fn ($row) => $row['item'] !== null)->values();

    if ($isCo2) {
        $technicalRows = collect([
            ['label' => 'Working Area', 'value' => $quotation->inputpower],
            ['label' => 'Laser Source', 'value' => $quotation->cncspan],
            ['label' => 'Max Working Speed', 'value' => $quotation->cnslenght],
            ['label' => 'Max Cutting Thickness', 'value' => $quotation->cuttingrang],
            ['label' => 'Blower (Air Ex)', 'value' => $quotation->liftingheight],
            ['label' => 'Power Supply', 'value' => $quotation->headquantity],
        ]);
    } else {
        $technicalRows = collect([
            ['label' => 'Input Power', 'value' => $quotation->inputpower],
            ['label' => 'Cutting Way', 'value' => $cuttingWay->cuttingway ?? null],
            ['label' => 'CNC Span (mm)', 'value' => $quotation->cncspan],
            ['label' => 'CNC Length (mm)', 'value' => $quotation->cnslenght],
            ['label' => 'Effective Cutting Range', 'value' => $quotation->cuttingrang],
            ['label' => 'Cutting Head Lifting Height (mm)', 'value' => $quotation->liftingheight],
            ['label' => 'Laser Cutting Head Quantity', 'value' => $quotation->headquantity],
            ['label' => 'Cutting Thickness (mm)', 'value' => $cuttingThickness->cuttingthinks ?? null],
            ['label' => 'Idle Stroke Speed', 'value' => $quotation->strokespeed],
            ['label' => 'Cutting Speed', 'value' => $quotation->cuttingspeed],
            ['label' => 'Drive', 'value' => $quotation->drive],
        ]);
    }
    $technicalRows = $technicalRows->filter(fn ($row) => filled($row['value']))->values();

    $grandTotal = $quotation->items->sum(fn ($item) => (float) $item->amount);
@endphp

<div class = "container-fulid">
    <div class="row">
        <div class="col-xxl-12">
            <div class=" html-content" id="demo">
                <div class = "row">
                    <div class = "col-md-6">
                        <img src="{{ URL::asset('build/images/header.png') }}" alt="" width="300">
                    </div>
                    <div class = "col-md-6">

                    </div>
                </div>
                <br>

            <div class="row">
                <div class = "col-md-12">
                    <p>To</p><br>
                    <p>{{ strtoupper($quotation->companyname ?: $quotation->clientname ?: '-') }}<br>
                      {{ $quotation->companyaddress ?: '-' }}<br>
                      @if($quotation->phone)PH. NO. : {{ $quotation->phone }}@endif</p>
                </div>


            </div>
            <br>
            <div class="row">
                <div class = "col-md-12">
                    <p>Kind Atten. – {{ $quotation->clientname ?: '-' }}<br>
                        @if($quotation->phone)Mobile: - {{ $quotation->phone }}<br>@endif
                        @if($quotation->email)Email: - {{ $quotation->email }}@endif</p><br>
                        <p>Subject: - Proposal &amp; Quotation for {{ $productLabel }}</p><br>
                        <p>Dear Sir/Madam,<br>
                            Thank you very much for your valuable enquiry and the confidence you have showed in us. This is in continuation of our
                            technical discussion on subject matter, we are glad to submit our budgetary techno-commercial offer for the design,
                            engineering, supply of the {{ $productLabel }} as desired by you.
                            We trust that our offer is in line with your requirement and for any further clarification, please contact the undersigned.
                            Thanking you and looking forward to receiving your valued order at the earliest.
                            </p>
                </div>


            </div>
            <br>
            <div class ="row">
                <div class ="col-md-12">
                    <hr style =  "border: 1px solid green;">

                    <p style = "text-align:center;">
                        12 Whaghodia GIDC 1st Gate Whaghodia Road, Vadodara - 391760<br>
                        Contact No. +91-7096487806<br>
                        Email- info@oraclemachinetech.com, www.oraclemachinetech.com
                    </p>
                </div>
            </div>
            <br>

            @if($configRows->isNotEmpty())
            <p style="page-break-after: always;">&nbsp;</p>
            <div class = "row">
                <div class = "col-md-6">
                    <img src="{{ URL::asset('build/images/header.png') }}" alt="" width="300">
                </div>
                <div class = "col-md-6">

                </div>
            </div>
            <br>
            <div class ="row">
                <div class = "col-md-12">
                    <h5>Standard Configuration</h5>
                    <table class ="table table-bordered dt-responsive nowrap table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Sr. No</th>
                                <th>Description</th>
                                <th>Make &amp; Model</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($configRows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row['label'] }}</td>
                                <td>
                                    {{ $row['item']->company ?? $row['item']->{$row['nameField']} }}
                                    @if($row['nameField'] === 'modal' && $row['item']->modal)
                                        - {{ $row['item']->modal }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
            <div class ="row">
                <div class ="col-md-12">
                    <hr style =  "border: 1px solid green;">

                    <p style = "text-align:center;">
                        12 Whaghodia GIDC 1st Gate Whaghodia Road, Vadodara - 391760<br>
                        Contact No. +91-7096487806<br>
                        Email- info@oraclemachinetech.com, www.oraclemachinetech.com
                    </p>
                </div>
            </div>
            @endif

            @if($technicalRows->isNotEmpty())
            <p style="page-break-after: always;">&nbsp;</p>
            <div class = "row">
                <div class = "col-md-6">
                    <img src="{{ URL::asset('build/images/header.png') }}" alt="" width="300">
                </div>
                <div class = "col-md-6">

                </div>
            </div>
            <br>
            <div class ="row">
                <div class = "col-md-12">
                    <h5>Technical Parameters</h5>
                    <table class ="table table-bordered dt-responsive nowrap table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Sr. No</th>
                                <th>Description</th>
                                <th>Specification</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($technicalRows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['value'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
            <div class ="row">
                <div class ="col-md-12">
                    <hr style =  "border: 1px solid green;">

                    <p style = "text-align:center;">
                        12 Whaghodia GIDC 1st Gate Whaghodia Road, Vadodara - 391760<br>
                        Contact No. +91-7096487806<br>
                        Email- info@oraclemachinetech.com, www.oraclemachinetech.com
                    </p>
                </div>
            </div>
            @endif

            <p style="page-break-after: always;">&nbsp;</p>
            <div class = "row">
                <div class = "col-md-6">
                    <img src="{{ URL::asset('build/images/header.png') }}" alt="" width="300">
                </div>
                <div class = "col-md-6">

                </div>
            </div>

            <br>
            <div class ="row">
                <div class = "col-md-12">
                    <h5>Power Requirement</h5>
                    <table class ="table table-bordered dt-responsive nowrap table-striped align-middle">
                        <tbody>
                            <tr>
                                <th scope="row" style="text-align: left; font-weight: normal;">Power</th>
                                <td>3 Phase AC 380V 50 Hz</td>
                            </tr>
                            <tr>
                                <th scope="row" style="text-align: left; font-weight: normal;">Protection Level of Total Power Supply</th>
                                <td>IP 54</td>
                            </tr>
                            <tr>
                                <th scope="row" style="text-align: left; font-weight: normal;">Power voltage required </th>
                                <td>220V±5%,415V±5%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class ="row">
                <div class = "col-md-12">
                    <h5>Tools &amp; Consumable List</h5>
                    <table class ="table table-bordered dt-responsive nowrap table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Sr. No</th>
                                <th>Consumables and Tools</th>
                                <th>Qty /Units</th>
                            </tr>
                        </thead>
                            <tbody>
                            <tr>
                                <td>1</td>
                                <td>Lower protection glass</td>
                                <td>05</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Nozzle</td>
                                <td>05</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Protective Eye wear</td>
                                <td>05</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class ="row">
                <div class ="col-md-12">
                    <hr style =  "border: 1px solid green;">

                    <p style = "text-align:center;">
                        12 Whaghodia GIDC 1st Gate Whaghodia Road, Vadodara - 391760<br>
                        Contact No. +91-7096487806<br>
                        Email- info@oraclemachinetech.com, www.oraclemachinetech.com
                    </p>
                </div>
            </div>
            <p style="page-break-after: always;">&nbsp;</p>
            <div class = "row">
                <div class = "col-md-6">
                    <img src="{{ URL::asset('build/images/header.png') }}" alt="" width="300">
                </div>
                <div class = "col-md-6">

                </div>
            </div>
            <br>
            <div class = "row">
                <div class = "col-md-12">
                   <h5>Customers Scope of work</h5>
                    <p>1).Earthing-Two chemical Earthing pits required close to the machine. Earth resistance should be less than 0.5
                        Ohm, leakage current less than 5mA. Minimum 6 square mm copper wire to be used for earth connection to the
                        machine<br>
                        2). Switch box-220V 2phases, 415V,3 phases, five wire, High pressure pipe joint, Gas pipe as per your cutting
                        materials, Gas Regulators, Air Compressor, Online UPS, Voltage Stabilizer, Isolation Transformer all are depend as
                        per our requirements.<br>
                        3).Power supply as per required up to our control panel is to be provided by you.<br>
                        4).Any civil / structural / fabrication work required for mounting of the system and cutouts to the machine cabinet
                        etc. to be organized locally from your end.<br>
                        5).Loading / unloading of systems at locations to be organized from your side.<br>
                        6).Required Suitable capacity of crane/ fork lift/ at the time of erection/ installation provided by you</p>
                </div>
            </div>
            <br>
            <div class = "row">
                <div class = "col-md-12">
                    <div class="row"style="border-left: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;border-top: 1px solid black;">
                        <div class="col-12">

                            <div class="row" >

                                <div class="col-3" >
                                <div>Name</div>
                                <div>Address</div>
                                <div>GST No</div>
                            </div>
                            <div class="col-9">
                                <div style  = "text-transform: capitalize;"> : {{ $recipientName ?: '-' }}</div>
                                <div style  = "text-transform: capitalize;"> : {{ $quotation->companyaddress ?: '-' }}</div>
                                <div style  = "text-transform: capitalize;"> : {{ $quotation->gstno ?: '-' }}</div>


                            </div>
                        </div>
                        </div>
                        <table class="table1 table-bordered p-0"  style="border-right:1px solid;border-left:1px solid;border-bottom:1px solid;">
                            <thead class="">
                              <tr  style="vertical-align: middle;">
                                <th scope="col">#</th>
                                <th scope="col">Name of Product</th>
                                <th scope="col">Total Amount</th>
                              </tr>

                            </thead>
                            <tbody style= "text-align:right">
                              @forelse($quotation->items as $index => $item)
                              <tr>
                                <td>{{ $index + 1 }}</td>
                                <td style ="text-transform: capitalize;text-align:left">{{ $item->description ?: '-' }}</td>
                                <td>{{ \App\Support\IndianNumber::format($item->amount) }}</td>
                              </tr>
                              @empty
                              <tr>
                                <td>1</td>
                                <td style ="text-transform: capitalize;text-align:left">{{ $productLabel }}</td>
                                <td>{{ \App\Support\IndianNumber::format(0) }}</td>
                              </tr>
                              @endforelse
                              <tr>
                                <td></td>
                                <td>Grand Total</td>
                                <td>{{ \App\Support\IndianNumber::format($grandTotal) }}</td>
                              </tr>

                            </tbody>

                          </table>
                          <div class = "row">
                            <div class = "col-md-6">
                                <div class="row" >

                                    <div class="col-3" >
                                    <div>Bank Name</div>
                                    <div>Bank Account Number</div>
                                    <div>Bank IFSC Code</div>
                                </div>
                                <div class="col-3">
                                    <div style  = "text-transform: capitalize;"> : {{ $bank->bankname ?? 'Not specified' }}</div>
                                    <div style  = "text-transform: capitalize;"> : {{ $bank->bankaccountnumber ?? '-' }}</div>
                                    <div style  = "text-transform: capitalize;"> : {{ $bank->bankifsccode ?? '-' }}</div>


                                </div>
                            </div>
                          </div>

                      </div>

                    </div>
                </div>
            </div>
            <br>
            <div class ="row">
                <div class ="col-md-12">
                    <hr style =  "border: 1px solid green;">

                    <p style = "text-align:center;">
                        12 Whaghodia GIDC 1st Gate Whaghodia Road, Vadodara - 391760<br>
                        Contact No. +91-7096487806<br>
                        Email- info@oraclemachinetech.com, www.oraclemachinetech.com
                    </p>
                </div>
            </div>
            <br>
            <p style="page-break-after: always;">&nbsp;</p>
            <div class = "row">
                <div class = "col-md-6">
                    <img src="{{ URL::asset('build/images/header.png') }}" alt="" width="300">
                </div>
                <div class = "col-md-6">

                </div>
            </div>
            <br>
            <div class = "row">
                <div class = "col-md-12">
                   <h5>Our Supply does not include the following:</h5>
                    <p>1) Civil work like Pits, control room, shed, cable trench, etc<br>
                        2) Any item which is not specified in the scope of supply but required for successful commissioning please provide.<br>
                        3) Only FAT will be conducted on without charges for SAT and pre-commissioning &amp; final commissioning will be
                        conducted its depend on our contract and arrangements. <br>
                        4) After Final Commissioning any kind of service and support will be on charge basis. Its depend on our Warranty
                        terms and Condition.
                      </p>
                </div>
            </div>
            <br>
            <div class = "row">
                <div class = "col-md-12">
                   <h5>Commercial Terms and conditions::</h5>
                    <p>Prices : Ex-works<br>
                        Packing &amp; Forwarding : Extra @3 % on above price<br>
                        IGST : Extra As applicable at the time of dispatch<br>
                        Freight : Extra at actual<br>
                        Delivery : 8 to 12 weeks from the date of receipt of P.O. and advance<br>
                        Design &amp; Engineering : Nil<br>
                        Payment Terms : 50% advance along with PO &amp; 50% before delivery.<br>
                        Warranty : Item Supplied shall be under warranty for a period of 12 months from the
                         Date of installation &amp; Commissioning towards any manufacturing defects.
                         Note:- The optics in the laser head and fiber cable will not be covered under
                         Warranty if found to be contaminated or physically damaged. <br>
                        Installation &amp; commissioning : At our Scope<br>
                        a) Installation work will be carried out only for above supplied items.<br>
                        b) Lodging &amp; Boarding in your account.<br>
                        c) Site should be ready for installation and commissioning before sending our
                        team.<br>
                        d) In case if any reason site not ready for installation and commissioning then
                        our team shall wait for 3 Days’ time after 3 Days additional charges will be
                        applicable. <br>
                        Validity : 15 days from date of quotation.
                      </p>
                </div>
            </div>
            <br>
            <div class ="row">
                <div class ="col-md-12">
                    <hr style =  "border: 1px solid green;">

                    <p style = "text-align:center;">
                        12 Whaghodia GIDC 1st Gate Whaghodia Road, Vadodara - 391760<br>
                        Contact No. +91-7096487806<br>
                        Email- info@oraclemachinetech.com, www.oraclemachinetech.com
                    </p>
                </div>
            </div>

        </div>
    </div>
    </div>



</div>
@endsection
@section('script')

<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
