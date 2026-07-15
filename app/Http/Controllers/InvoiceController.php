<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Bank;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Invoiceproduct;
use App\Models\Paidamount;



class InvoiceController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $invoice = Invoice::join('customer', 'invoice.id', '=', 'customer.invoice_id','left')->
        orderBy('id' ,'desc')->get(['invoice.*', 'customer.name' ,'customer.mobile']);

        return view('invoice' ,compact('invoice'));
    }

    /**
     * DataTables server-side data source for apps-invoices-list.blade.php.
     * Mirrors the exact row query and per-cell HTML the view used to
     * render itself, now paginated/filtered/sorted server-side instead
     * of shipping every row to the browser on every request.
     */
    public function listData(Request $request)
    {
        $columns = ['invoice.id', 'customer.name', 'customer.phone', 'invoice.date', 'invoice.amount', 'invoice.paidamount', 'invoice.remaining_amount'];

        $baseQuery = fn () => Invoice::join('customer', 'invoice.id', '=', 'customer.invoice_id', 'left');

        $recordsTotal = $baseQuery()->count();

        $query = $baseQuery()->select(['invoice.*', 'customer.name', 'customer.phone', 'customer.id as customer_id']);

        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        $recordsFiltered = (clone $query)->count();

        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        if ($orderColumnIndex !== null && isset($columns[$orderColumnIndex])) {
            $query->orderBy($columns[$orderColumnIndex], $orderDir);
        } else {
            $query->orderBy('invoice.id', 'desc');
        }

        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 10);
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $rows = $query->get();

        $data = $rows->map(function ($item) {
            if ($item->amount == $item->paidamount) {
                $statusHtml = '<span  class = "badge bg-success-subtle text-success text-uppercase">Paid</span>';
                $paymentButton = '<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-id="' . e($item->id) . '" id="savepayment" data-bs-target="#exampleModalgrid" style="display: none;">Payment</button>';
            } else {
                $statusHtml = '<span  class = "badge bg-warning-subtle text-warning text-uppercase">Pending</span>';
                $paymentButton = '<button type="button" class="btn btn-sm btn-primary open-modal" data-id="' . e($item->id) . '" data-customer="' . e($item->customer_id) . '"data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Payment</button>';
            }

            $actionHtml = '<div class="d-flex gap-2">'
                . '<div class="edit"><a href="' . route('invoice.details', $item->id) . '"><button class="btn btn-sm btn-success edit-item-btn">Details</button></a></div>'
                . '<div class="remove">' . $paymentButton . '</div>'
                . '</div>';

            return [
                $item->id,
                $item->name,
                $item->phone,
                $item->date,
                $item->amount,
                $item->paidamount,
                $item->remaining_amount,
                $statusHtml,
                $actionHtml,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function create(Request $request)
    {
        $product = Product::orderBy('id' ,'desc')->get();
        $bank = Bank ::get();
       
        return view('apps-invoices-create' ,compact('product' ,'bank' ));
    }
    public function getproduct(Request $request)
    {
        
        $product = Product ::orderBy('id' ,'desc')->get();
       

        return response()->json([
            'isSuccess' => true,
            "product"=> $product,
        ], 200);
    }
    public function getproductvalue1(Request $request)
    {
        $paymentType1 = $request->input('paymentType1');
        $product1 = Product ::where('id' ,$paymentType1)->get();
        $make = $product1[0]->make;
        $rate = $product1[0]->rate;
        $unit = $product1[0]->unit;

        

        return response()->json([
            'isSuccess' => true,
            "product1"=> $product1,
            "make" =>$make,
            "unit" =>$unit,
            "rate" =>$rate,


            

           
        
        
        ], 200);
    }
    public function store(Request $request)
    {
        $input['invoice_id'] =$request->input('invoice_id');
        $input['date'] = $request->input('invoice_date');
        $input['paycondition'] = $request->input('paycondition');
        $input['duedate'] = $request->input('duedate');
        $input['placesupply'] = $request->input('placesupply');
        $input['challanno'] = $request->input('challanno');
        $input['pono'] = $request->input('ponumber');
        $input['ewaybillno'] = $request->input('ewaybillno');
        $input['ewaybilldate'] = $request->input('ewaybilldate');
        $input['despatchthrough'] = $request->input('despatchthrough');
        $input['accountholder'] = $request->input('payment_details_card_holder_name');
        $input['bankaccountnumber'] = $request->input('payment_details_card_number');
        $input['bankifsccode'] = $request->input('payment_details_card_ifsc_code');
        $input['bankname'] = $request->input('payment_details_bank_name');
        $input['bankbranchname'] = $request->input('payment_details_branch_name');
        $input['note'] = $request->input('notes');
        $input['totalamountbeforetax'] = $request->input('order_summary_cart_totalbeforetax');
        $input['amount'] = $request->input('order_summary_cart_amount');
        $input['amountwithtax'] = $request->input('order_summary_cart_total');
        $input['companyaddress'] = $request->input('company_details_companyAddress');
        $input['companyphone'] = $request->input('company_details_contact_no');
        $input['companywebsite'] = $request->input('company_details_Website');
        $input['companyemail'] = $request->input('company_details_email');
        $input['companyzipcode'] = $request->input('company_details_companyaddpostalcode');
        $input['TransportVehicleNo'] = $request->input('transportvehicle');


       
        $create = Invoice::create($input);
        $id  = $create->id;
        $input1['invoice_id'] = $id;
        $input1['name'] = $request->input('billing_address_full_name');
        $input1['address'] =$request->input('billing_address_address');
        $input1['phone'] = $request->input('billing_address_phone');
        $input1['state'] = $request->input('billing_state');
       
        $input1['billinggst'] = $request->input('billing_gst');
        $input1['billingpan'] = $request->input('billing_pan');
        $input1['sname'] = $request->input('shipping_address_full_name');
        $input1['saddress'] =$request->input('shipping_address_address');
        $input1['sphone'] = $request->input('shipping_address_phone');
        $input1['sstate'] = $request->input('shipping_state');
        $input1['shippinggst'] = $request->input('shipping_gst');
        $input1['shippingpan'] = $request->input('shipping_pan');
        
        
        Customer::create($input1);
        $product =$request->input('new_product_obj');
       
       
        foreach ($product as  $products) {
            if (!empty( $products['product_name'])) {
                $pickupDropLocationData  = [
                    'invoice_id' =>  $id,
                    'product_name' =>  $products['product_name'],
                    'hsn' =>  $products['hsn'],
                    'unit' =>$products['unit'],
                    'rate' =>  $products['product_rate'],
                    'quantity' =>  $products['product_qty'],
                    'total' =>  $products['product_price'],
                    'gst' => $products['gst'],
                    'gstamount' =>  $products['withtax'],
                    'totalamount' =>  $products['total'],
                    
                 ];
                
              # insert the tourPickDropLocations.
                Invoiceproduct::create($pickupDropLocationData);
            }
        }
        return response()->json([
            'isSuccess' => true,
            
    
           
        
        
        ], 200); // Status code here
    }
        public function details($id)
        {
            $customer = Customer::where('invoice_id' ,$id)->get();
            $state = $customer[0]->state;
            $invoice = Invoice::where('id' ,$id)->get();
            $totalamountwithtax = $invoice[0]->amountwithtax;
            $amount = $invoice[0]->amount;
            $roundof =ROUND($amount);
            $sgstamount = 0;
            $cgstamount = 0;
            $igsamount = 0;
            if ($state != "Gujarat") {
                $sgstamount = $totalamountwithtax * 0.09;
                $cgstamount = $totalamountwithtax * 0.09;
            } else {
                $igsamount = $totalamountwithtax*0.18;
            }
            $invoiceproduct = Invoiceproduct::join('product', 'invoiceproduct.product_name', '=', 'product.id','left')->where('invoice_id' ,$id)->get(['invoiceproduct.*', 'product.name as product']);
            return view('apps-invoices-details' ,compact('roundof','sgstamount','cgstamount','state','invoice' ,'customer','invoiceproduct' ,'totalamountwithtax','amount','igsamount'));
        }
        public function updatePayment(Request $request)
        {
            
            // Get the ID and paid amount from the request
            $itemId = $request->input('id');
            $customer_id = $request->input('customer_id');
            
            $paidAmount = $request->input('paidAmount');
                       // Find the item by ID
            $item = Invoice::find($itemId);
            $input2['invoice_id'] =   $itemId;
            
            $input2['customer_id'] = $customer_id;
            $input2['paidAmount'] = $request->input('paidAmount');
            
            Paidamount::create($input2);
            if ($item) {
                // Calculate the new total paid amount by adding the new payment to the existing paid amount
                $newPaidAmount = $item->paidamount + $paidAmount;
                
                // Calculate the remaining amount after the payment
                $remainingAmount = $item->amount - $newPaidAmount;
                
                // Update the item in the database with the new paid amount and remaining amount
                $item->paidamount = $newPaidAmount;
                $item->remaining_amount = $remainingAmount;
                
                $item->save();
        
                // Return a success response
                return response()->json(['success' => true]);
            }
        
            // Return a failure response if the item is not found
            return response()->json(['success' => false, 'message' => 'Item not found']);
        }
        public function paymenthistry()
        {

        $Paidamount = Paidamount::leftJoin('invoice', 'paidamount.invoice_id', '=', 'invoice.id')
        ->leftJoin('customer', 'paidamount.customer_id', '=', 'customer.id')
        ->orderBy('paidamount.id' ,'desc')->get(['paidamount.*', 'customer.name as cname']);

        return view('paymenthistry' ,compact('Paidamount'));
        }
        public function vender()
        {
        $vender = Invoice::join('customer', 'invoice.customer_id', '=', 'customer.id','left')
            ->orderBy('invoice.id', 'desc')
            ->get(['invoice.*', 'invoice.invoice_id as invoice_id', 'customer.name as cname']);
        return view('vender' ,compact('vender'));
        }
        
        

      
           
               
            
       
    }
   
   

    



