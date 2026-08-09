<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Invoice\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $service)
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        return view('apps-invoices-list');
    }

    public function listData(Request $request)
    {
        return response()->json($this->service->getDataTableResponse($request));
    }

    public function create(Request $request)
    {
        return view('apps-invoices-create', $this->service->getCreateViewData());
    }

    public function getproduct(Request $request)
    {
        return response()->json([
            'isSuccess' => true,
            'product' => $this->service->getProducts(),
        ], 200);
    }

    public function getproductvalue1(Request $request)
    {
        $pricing = $this->service->getProductPricingInfo($request->input('paymentType1'));

        return response()->json(array_merge(['isSuccess' => true], $pricing), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'placesupply' => 'required|string|max:255',
            'billing_state' => ['required', 'string', \Illuminate\Validation\Rule::in(\App\Support\IndianStates::LIST)],
            'shipping_state' => ['required', 'string', \Illuminate\Validation\Rule::in(\App\Support\IndianStates::LIST)],
        ]);

        $this->service->createInvoiceWithDetails($request);

        return response()->json(['isSuccess' => true], 200);
    }

    public function details($id)
    {
        return view('apps-invoices-details', $this->service->getInvoiceDetails($id));
    }

    public function update(Request $request, $id)
    {
        $invoiceData = $request->validate([
            'bankaccountnumber' => 'nullable|string|max:255',
            'bankifsccode' => 'nullable|string|max:255',
            'accountholder' => 'nullable|string|max:255',
            'bankname' => 'nullable|string|max:255',
            'bankbranchname' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'paycondition' => 'nullable|string|max:255',
            'duedate' => 'nullable|date',
            'placesupply' => 'nullable|string|max:255',
            'challanno' => 'nullable|string|max:255',
            'pono' => 'nullable|string|max:255',
            'ewaybillno' => 'nullable|string|max:255',
            'ewaybilldate' => 'nullable|date',
            'despatchthrough' => 'nullable|string|max:255',
            'TransportVehicleNo' => 'nullable|string|max:255',
        ]);

        $customerData = $request->validate([
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:2000',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'state' => ['nullable', 'string', \Illuminate\Validation\Rule::in(\App\Support\IndianStates::LIST)],
            'billinggst' => 'nullable|string|max:255',
            'billingpan' => 'nullable|string|max:255',
        ]);

        $this->service->updateDetails((int) $id, $invoiceData, $customerData);

        return redirect()->route('invoice.details', $id)->with('success', 'Invoice updated.');
    }

    public function destroy($id)
    {
        $this->service->deleteInvoice((int) $id);

        return redirect()->route('invoice')->with('success', 'Invoice deleted.');
    }

    public function pdf($id)
    {
        abort_unless(Invoice::where('id', $id)->exists(), 404);

        $data = $this->service->getInvoiceDetails($id);
        $filename = 'Invoice-' . str_replace('/', '-', $data['invoice'][0]->invoice_id) . '.pdf';
        $pdf = Pdf::loadView('pdf.invoice', $data)->setPaper('a4');

        return $pdf->download($filename);
    }

    public function updatePayment(Request $request)
    {
        // Bounded rather than just "numeric": a raw, unbounded value here is
        // exactly how a QA fuzz-test payload (67978778979789) once corrupted
        // a real invoice's paid/remaining totals.
        $data = $request->validate([
            'id' => 'required|integer|exists:invoice,id',
            'customer_id' => 'required|integer|exists:customer,id',
            'paidAmount' => 'required|numeric|min:0.01|max:99999999.99',
            'payment_method' => 'required|in:cash,bank_transfer,upi,cheque',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $success = $this->service->recordPayment($data);

        if ($success) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Item not found']);
    }

    public function paymenthistry()
    {
        return view('paymenthistry', ['Paidamount' => $this->service->getPaymentHistory()]);
    }

    public function vender()
    {
        return view('vender');
    }
}
