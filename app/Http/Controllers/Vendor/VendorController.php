<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorPayment;
use App\Services\Expenses\VendorPayableService;
use App\Services\Vendor\VendorService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct(
        private VendorService $service,
        private VendorPayableService $payableService,
    ) {
    }

    public function index()
    {
        return view('vendor.index', [
            'vendors' => $this->service->listAll(),
        ]);
    }

    public function paymentsIndex(Request $request)
    {
        return view('vendor.payments-index', [
            'payments' => $this->payableService->forDateRange($request->input('from'), $request->input('to')),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ]);
    }

    public function show($id)
    {
        $vendor = $this->service->find((int) $id);
        abort_if(! $vendor, 404);

        return view('vendor.show', [
            'vendor' => $vendor,
            'bills' => $vendor->bills()->latest('date')->get(),
            'payments' => $vendor->payments()->latest('date')->get(),
        ]);
    }

    public function storeBill(Request $request, $id)
    {
        $vendor = $this->service->find((int) $id);
        abort_if(! $vendor, 404);

        $data = $request->validate([
            'bill_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string|max:2000',
        ]);

        $this->payableService->createBill($vendor, $data, $request->user());

        return redirect()->route('admin.vendors.show', $vendor->id)->with('success', 'Bill recorded.');
    }

    public function storePayment(Request $request, $id)
    {
        $vendor = $this->service->find((int) $id);
        abort_if(! $vendor, 404);

        $data = $request->validate([
            'vendor_bill_id' => 'nullable|integer',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_mode' => 'required|in:cash,bank,upi,cheque',
            'description' => 'nullable|string|max:2000',
        ]);

        try {
            $this->payableService->recordPayment($vendor, $data, $request->user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return redirect()->route('admin.vendors.show', $vendor->id)->with('success', 'Payment recorded.');
    }

    public function updateBill(Request $request, $id, $billId)
    {
        $bill = VendorBill::where('vendor_id', $id)->findOrFail($billId);

        $data = $request->validate([
            'bill_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string|max:2000',
        ]);

        $this->payableService->updateBill($bill, $data);

        return redirect()->route('admin.vendors.show', $id)->with('success', 'Bill updated.');
    }

    public function destroyBill($id, $billId)
    {
        $bill = VendorBill::where('vendor_id', $id)->findOrFail($billId);
        $this->payableService->deleteBill($bill);

        return redirect()->route('admin.vendors.show', $id)->with('success', 'Bill deleted.');
    }

    public function updatePaymentRecord(Request $request, $id, $paymentId)
    {
        $payment = VendorPayment::where('vendor_id', $id)->findOrFail($paymentId);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_mode' => 'required|in:cash,bank,upi,cheque',
            'description' => 'nullable|string|max:2000',
        ]);

        $this->payableService->updatePayment($payment, $data);

        return redirect()->route('admin.vendors.show', $id)->with('success', 'Payment updated.');
    }

    public function destroyPaymentRecord($id, $paymentId)
    {
        $payment = VendorPayment::where('vendor_id', $id)->findOrFail($paymentId);
        $this->payableService->deletePayment($payment);

        return redirect()->route('admin.vendors.show', $id)->with('success', 'Payment deleted.');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->service->create($data);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created.');
    }

    public function update(Request $request, $id)
    {
        $vendor = $this->service->find((int) $id);
        abort_if(! $vendor, 404);

        $data = $this->validated($request);
        $this->service->update($vendor, $data);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated.');
    }

    public function destroy($id)
    {
        $vendor = $this->service->find((int) $id);
        abort_if(! $vendor, 404);

        $this->service->delete($vendor);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', Vendor::CATEGORIES),
            'gstin' => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);
    }
}
