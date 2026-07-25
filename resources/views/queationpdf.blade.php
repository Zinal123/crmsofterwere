@extends('layouts.master')
@section('title') Quotation @endsection
@section('css')
<style>

   .table1{
    margin-bottom: 0rem !important;
    padding: 0rem !important;
   }
</style>
<script src="http://code.jquery.com/jquery-1.11.0.min.js" integrity="sha384-/Gm+ur33q/W+9ANGYwB2Q4V0ZWApToOzRuA8md/1p9xMMxpqnlguMvk8QuEFWA1B" crossorigin="anonymous"></script>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Quation @endslot
@slot('title') Quation Details @endslot
@endcomponent

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
                    <p>JALASAI ENTERPRISES<br>
                      RAJKOT, GUJARAT, INDIA<br>
                      PH. NO. : 799071183</p>
                </div>
                
               
            </div>
            <br>
            <div class="row">
                <div class = "col-md-12">
                    <p>Kind Atten. – Raj Patel Sir<br>
                        Mobile: -7990711831<br>
                        Email: - rajpatel15396@gmail.com</p><br>
                        <p>Subject: - Proposal & Quotation for 6 KW CNC Fiber Laser Cutting Machine Size is 2 x 6.3 Mtr</p><br>
                        <p>Dear Sir,<br>
                            Thank you very much for your valuable enquiry and the confidence you have showed in us. This is in continuation of our
                            technical discussion on subject matter, we are glad to submit our budgetary techno-commercial offer for the design,
                            engineering, supply of 6 KW CNC Laser Fiber Cutting Machine as desired by you.
                            We trust that our offer is in line with your requirement and for any further clarification, please contact the undersigned.
                            Thanking you and looking forward to receiving your values order at the earliest.
                            </p>
                </div>
                
               
            </div>
            <br>
            <div class ="row">
                <div class ="col-md-12">
                    <hr style =  "border: 1px solid green;">
                     
                    <p style = "text-align:center;">
                        Gat No.77 Opp Deepak Enterprises Jyotiba Nagar, Talawade Pune-411062.<br>
                        Corporate Mo. No- 8483918902, 8483088902,<br>
                        Email- sales@technolinksolution.com/salestechnolinksolutions@gmail.com, www.technolinksolutions.com
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
            <div class ="row">
                <div class = "col-md-12">
                    <h5>Standard Configuration</h5>
                    <table class ="table table-bordered dt-responsive nowrap table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Sr. No</th>
                                <th>Description</th>
                                <th>Specification</th>
                                <th>Product Photo</th>

                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Fiber Laser</td>
                                <td>Make:-6000W Max Photonics.
                                    Fiber laser is a high-power fiber laser
                                    with high electro- optical conversion
                                    efficiency, compact size, and good beam
                                    quality and maintenance-free. Cutting,
                                    welding and other process quality, widely
                                    used in laser cutting, laser welding, laser
                                    cladding, laser brazing, laser surface heat
                                    treatment, etc.</td>
                                <td> <img src="{{ URL::asset('build/images/fiberlaser.jpeg') }}" alt="" width = "50" height="50"></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Laser cutting head</td>
                                <td>BM114, Ray tool Auto Focus,
                                    Switzerland.
                                    1).This kind laser head has a strong
                                    advantage on medium power large
                                    format fiber laser cutting application.
                                    2).Completely sealed internal structure
                                    of laser head can avoid optical part
                                    polluted by dust.
                                    3)Two point centering adjustment of
                                    laser head; the focus adjusting take
                                    imported motor driving and has great
                                    Improvement in perforation.
                                    4).Protective lens take more convenient
                                    replacement drawer installation way.
                                    5).Can be equipped with various kind of
                                    QBH connectors laser machin</td>
                                    <td> <img src="{{ URL::asset('build/images/lasercuttinghead.jpeg') }}" alt="" width = "50" height="50"></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Laser chiller</td>
                                <td>S & A
                                    Large cooling capacity, stable
                                    performance, trouble-free, clean water
                                    quality, good heat exchange effect with
                                    fiber laser, and linkage signal to protect
                                    the laser</td>
                                    <td> <img src="{{ URL::asset('build/images/softwere.jpeg') }}" alt="" width = "50" height="50"></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>CNC system/Software</td>
                                <td>Cypcut FSCUT 2000
                                    Laser cutting control system from
                                    Shanghai BaiChu (BC) Electronic
                                    technology co., LTD.
                                    This is the most popular and reliable
                                    control system used in the field of metal
                                    and non metal laser cutting</td>
                                    <td> <img src="{{ URL::asset('build/images/softerwere2.jpeg') }}" alt="" width = "50" height="50"></td>
                            </tr>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Capacitive THC</td>
                            <td>Baichu -Cypcut
                                High precision, long life, can provide
                                rigorous support for quenching helical
                                gears and grinding helical gears, so that
                                the load drive structure is compact, can
                                effectively reduce the driving torque</td>
                                <td> <img src="{{ URL::asset('build/images/softwere3.jpeg') }}" alt="" width = "50" height="50"></td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Driving System</td>
                            <td>Delta(Taiwan) / Yaskawa (Japan)
                                Servo motors and drives enable more
                                precision and shortest settling times.
                                Each model is equipped with a package
                                of software algorithms that tune
                                automatically, suppress vibration, and
                                compensate for friction and ripple and
                                effects to ensure smooth operation
                                without vibration. For friction and ripple
                                and effects to ensure smooth operation
                                without vibration</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>Rack & Pinion</td>
                            <td>YYC
                                Taiwan YYC precision gear rack</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>Linear Guide</td>
                            <td>HIWIN
                                Lead screw, guide rail lubrication and
                                seal protection system</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>Gear Boxes</td>
                            <td>himpo / Motovario
                                Servo motors and drives enable more
                                precision and shortest settling times.
                                Each model is equipped with a package
                                of software algorithms that tune
                                automatically, suppress vibration, and
                                compensate for friction and ripple and
                                effects to ensure smooth operation
                                without vibration. For friction and ripple
                                and effects to ensure smooth operation
                                without vibration.</td>
                            <td></td>
                            <tr>
                                <td>10</td>
                                <td>Linear Guide</td>
                                <td>Standard make(TLS)
                                    Due to their ability to continually move
                                    gas and hot air while also blocking air
                                    contaminants, centrifugal industrial
                                    blowers are the most commonly
                                    employed type of industrial blower found
                                    in ventilation systems</td>
                                <td></td>
                            </tr>
                                    <tr>
                                        <td>11</td>
                                        <td>Lubrication System
                                            for LM guide ways</td>
                                        <td>Lubomatic
                                            Lubricating the guide rails of X axis.Y axis,
                                            Z axis automatically, which could reduce
                                            maintenance cost and save time
                                            significantly? Oiling time can be adjusted.
                                            According to processing.
                                            Amount, which is more humanized</td>
                                        <td></td>
                                    </tr>
                          
                           
                                <tr>
                                    <td>12</td>
                                    <td>Gas Circuit Control</td>
                                    <td>SMC, Japan
                                        1).pressure relief valves, throttle valves,
                                        one-way valves
                                        2).Pressure relay
                                        3). Cylinder, solenoid valve, pneumatic
                                        three parts
                                        4). Pressure gauges, joints, pipes, et</td>
                                    <td></td>
                                        <tr>
                                            <td>13</td>
                                            <td>Auxiliary Gas</td>
                                            <td>Oxygen, Nitrogen, Air</td>
                                            <td></td>
                                        </tr>
                                </tr>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
            <div class ="row">
                <div class ="col-md-12">
                    <hr style =  "border: 1px solid green;">
                     
                    <p style = "text-align:center;">
                        Gat No.77 Opp Deepak Enterprises Jyotiba Nagar, Talawade Pune-411062.<br>
                        Corporate Mo. No- 8483918902, 8483088902,<br>
                        Email- sales@technolinksolution.com/salestechnolinksolutions@gmail.com, www.technolinksolutions.com
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
                            <tr>
                                <td>1</td>
                                <td>Machine Cutting Size Maximum L X M mm</td>
                                <td>2000 mm X 6300 mm</td>
                                
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Machine Over All Dimension</td>
                                <td>L8250 X W3270 X H 2100 mm</td>
                                
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Effective Travel X- axis<br>
                                    Y- axis<br>
                                    Z-axis</td>
                                <td>2000 mm<br>
                                    6300 mm<br>
                                    300 mm
                                    </td>
                                
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Positional accuracy X- axis<br>
                                    Y- axis<br>
                                    Z-axi</td>
                                <td>±0.03 mm/m<br>
                                        ±0.03 mm/m<br>
                                        ±0.03 mm/m</td>
                               
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>Cutting Accuracy</td>
                                <td>ISO 9013 232</td>
                                
                            </tr>
                            <tr>
                                <td>6</td>
                                <td>Repeated positioning accuracy X- axis<br>
                                    Y- axis<br>
                                    Z-axis</td>
                                <td>±0.02 mm<br>
                                    ±0.02 mm<br>
                                    ±0.005 mm<td>
                            </tr>
                            <tr>
                                <td>7</td>
                                <td>Rapid positioning speed X- axis<br>
                                    Y- axis<br>
                                    Z-axi</td>
                                <td>100 m/min
                                    100 m/min
                                    30 m/min</td>
                               
                            </tr>
                           
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
            <div class ="row">
                <div class ="col-md-12">
                    <hr style =  "border: 1px solid green;">
                     
                    <p style = "text-align:center;">
                        Gat No.77 Opp Deepak Enterprises Jyotiba Nagar, Talawade Pune-411062.<br>
                        Corporate Mo. No- 8483918902, 8483088902,<br>
                        Email- sales@technolinksolution.com/salestechnolinksolutions@gmail.com, www.technolinksolutions.com
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
            <div class ="row">
                <div class = "col-md-12">
                    <h5>Power Parameter</h5>
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
                    <h5>Tools & Consumable List</h5>
                    <table class ="table table-bordered dt-responsive nowrap table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Sr. No</th>
                                <th>Consumables and Tools L</th>
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
                                <td>Protective Eye wer</td>
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
                        Gat No.77 Opp Deepak Enterprises Jyotiba Nagar, Talawade Pune-411062.<br>
                        Corporate Mo. No- 8483918902, 8483088902,<br>
                        Email- sales@technolinksolution.com/salestechnolinksolutions@gmail.com, www.technolinksolutions.com
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
                                <div style  = "text-transform: capitalize;"> : </div>
                                <div style  = "text-transform: capitalize;"> : </div>
                                <div style  = "text-transform: capitalize;"> :</div>
                                
                                
                            </div>
                        </div>
                        </div>
                        <table class="table1 table-bordered p-0"  style="border-right:1px solid;border-left:1px solid;border-bottom:1px solid;">
                            <thead class="">
                              <tr  style="vertical-align: middle;">
                                <th scope="col" rowspan="2">#</th>
                                <th scope="col" rowspan="2">Name of Product</th>
                                <th scope="col" rowspan="2">Total Amount</th>
                                
                              </tr>
                             
                            </thead>
                            <tbody style= "text-align:right">
                               
                              <tr>
                                <td>1</td>
                                <td style ="text-transform: capitalize;text-align:left">Fiber Laser Cutting</td>
                                
                                <td>27,0000/-</td>
                               
                               
                              </tr>
                              <tr>
                                <td rowpan="3"></td>
                                <td>Grand Total</td>
                                <td>27,0000/-</td>
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
                                    <div style  = "text-transform: capitalize;"> : ICICI Bank</div>
                                    <div style  = "text-transform: capitalize;"> : 780305000477</div>
                                    <div style  = "text-transform: capitalize;"> : ICIC0007803</div>
                                    
                                    
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
                        Gat No.77 Opp Deepak Enterprises Jyotiba Nagar, Talawade Pune-411062.<br>
                        Corporate Mo. No- 8483918902, 8483088902,<br>
                        Email- sales@technolinksolution.com/salestechnolinksolutions@gmail.com, www.technolinksolutions.com
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
                        3) Only FAT will be conducted on without charges for SAT and pre-commissioning & final commissioning will be
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
                    <p>Prices : Ex-works, Pune basis<br>
                        Packing & Forwarding : Extra @3 % on above price<br>
                        IGST : Extra As applicable at the time of dispatch<br>
                        Freight : Extra at actual<br>
                        Delivery : 8 to 12 weeks from the date of receipt of P.O. and advance<br>
                        Design & Engineering : Nil<br>
                        Payment Terms : 50% advance along with PO & 50% before delivery.<br>
                        Warranty : Item Supplied shall be under warranty for a period of 12 months from the
                         Date of installation & Commissioning towards any manufacturing defects.
                         Note:- The optics in the laser head and fiber cable will not be covered under
                         Warranty if found to be contaminated or physically damaged. <br>
                        Installation & commissioning : At our Scope<br>
                        a) Installation work will be carried out only for above supplied items.<br>
                        b) Lodging & Boarding in your account.<br>
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
                        Gat No.77 Opp Deepak Enterprises Jyotiba Nagar, Talawade Pune-411062.<br>
                        Corporate Mo. No- 8483918902, 8483088902,<br>
                        Email- sales@technolinksolution.com/salestechnolinksolutions@gmail.com, www.technolinksolutions.com
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
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js" integrity="sha384-l7aOEgTYxgJ0nn2MziQWZCvuvJ2PtNcP05R4QwEoHW+kIS1gFpzupcQ7WhAdRKuq" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js" integrity="sha384-ZZ1pncU3bQe8y31yfZdMFdSpttDoPmOZg2wguVK9almUodir1PghgT0eY7Mrty8H" crossorigin="anonymous"></script>

@endsection
