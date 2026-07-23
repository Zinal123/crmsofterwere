<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Services\Invoice\InvoiceService;
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

    public function updatePayment(Request $request)
    {
        // Bounded rather than just "numeric": a raw, unbounded value here is
        // exactly how a QA fuzz-test payload (67978778979789) once corrupted
        // a real invoice's paid/remaining totals.
        $data = $request->validate([
            'id' => 'required|integer|exists:invoice,id',
            'customer_id' => 'required|integer|exists:customer,id',
            'paidAmount' => 'required|numeric|min:0.01|max:99999999.99',
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
