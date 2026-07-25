<?php

namespace App\Services\Invoice;

use App\Repositories\Contracts\BankRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Support\IndianNumber;
use App\Support\IndianStates;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    private const DATATABLE_COLUMNS = ['invoice.id', 'customer.name', 'customer.phone', 'invoice.date', 'invoice.amount', 'invoice.paidamount', 'invoice.remaining_amount'];

    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private ProductRepositoryInterface $productRepository,
        private BankRepositoryInterface $bankRepository,
    ) {
    }

    public function getCreateViewData(): array
    {
        return [
            'product' => $this->productRepository->allOrderedByLatest(),
            'bank' => $this->bankRepository->all(),
            'states' => IndianStates::LIST,
        ];
    }

    public function getProducts(): Collection
    {
        return $this->productRepository->allOrderedByLatest();
    }

    public function getProductPricingInfo($productId): array
    {
        $product1 = collect([$this->productRepository->find($productId)]);

        return [
            'product1' => $product1,
            'make' => $product1[0]->make,
            'unit' => $product1[0]->unit,
            'rate' => $product1[0]->rate,
        ];
    }

    /**
     * DataTables server-side data source for apps-invoices-list.blade.php.
     * Mirrors the exact row query and per-cell HTML the view used to
     * render itself, now paginated/filtered/sorted server-side instead
     * of shipping every row to the browser on every request.
     */
    public function getDataTableResponse(Request $request): array
    {
        $columns = self::DATATABLE_COLUMNS;
        $search = $request->input('search.value');

        $recordsTotal = $this->repository->countAllInvoices();
        $recordsFiltered = $this->repository->countFilteredInvoices($columns, $search);

        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = ($orderColumnIndex !== null && isset($columns[$orderColumnIndex]))
            ? $columns[$orderColumnIndex]
            : 'invoice.id';

        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 10);

        $rows = $this->repository->getPaginatedInvoiceRows($columns, $search, $orderColumn, $orderDir, $start, $length);

        $data = $rows->map(function ($item) {
            if ($item->amount == $item->paidamount) {
                $statusHtml = \App\Support\StatusBadge::render('Paid', 'success', 'ri-checkbox-circle-line');
                $paymentButton = '<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-id="' . e($item->id) . '" id="savepayment" data-bs-target="#exampleModalgrid" style="display: none;">Payment</button>';
            } else {
                $statusHtml = \App\Support\StatusBadge::render('Pending', 'warning', 'ri-time-line');
                $paymentButton = '<button type="button" class="btn btn-sm btn-primary open-modal" data-id="' . e($item->id) . '" data-customer="' . e($item->customer_id) . '"data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Payment</button>';
            }

            $actionHtml = '<div class="d-flex gap-2">'
                . '<div class="edit"><a href="' . route('invoice.details', $item->id) . '"><button class="btn btn-sm btn-success edit-item-btn">Details</button></a></div>'
                . '<div class="remove">' . $paymentButton . '</div>'
                . '</div>';

            return [
                $item->id,
                e($item->name),
                e($item->phone),
                $item->date ? date('d-M-y', strtotime($item->date)) : $item->date,
                IndianNumber::format($item->amount),
                IndianNumber::format($item->paidamount),
                IndianNumber::format($item->remaining_amount),
                $statusHtml,
                $actionHtml,
            ];
        });

        return [
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    public function createInvoiceWithDetails(Request $request): void
    {
        DB::transaction(function () use ($request) {
            $input['invoice_id'] = $request->input('invoice_id');
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
            $input['notes'] = $request->input('notes');
            $input['totalamountbeforetax'] = $request->input('order_summary_cart_totalbeforetax');
            $input['amount'] = $request->input('order_summary_cart_amount');
            $input['amountwithtax'] = $request->input('order_summary_cart_total');
            // Without these, a new invoice's remaining_amount stays NULL until
            // the first payment is recorded - which hides the invoice details
            // page's "Record Payment" form (gated on remaining_amount > 0) for
            // every invoice until someone finds another way to record one.
            $input['paidamount'] = 0;
            $input['remaining_amount'] = $request->input('order_summary_cart_amount');
            $input['companyaddress'] = $request->input('company_details_companyAddress');
            $input['companyphone'] = $request->input('company_details_contact_no');
            $input['companywebsite'] = $request->input('company_details_Website');
            $input['companyemail'] = $request->input('company_details_email');
            $input['companyzipcode'] = $request->input('company_details_companyaddpostalcode');
            $input['TransportVehicleNo'] = $request->input('transportvehicle');

            $invoice = $this->repository->createInvoice($input);
            $id = $invoice->id;

            $input1['invoice_id'] = $id;
            $input1['name'] = $request->input('billing_address_full_name');
            $input1['address'] = $request->input('billing_address_address');
            $input1['phone'] = $request->input('billing_address_phone');
            $input1['state'] = $request->input('billing_state');
            $input1['billinggst'] = $request->input('billing_gst');
            $input1['billingpan'] = $request->input('billing_pan');
            $input1['sname'] = $request->input('shipping_address_full_name');
            $input1['saddress'] = $request->input('shipping_address_address');
            $input1['sphone'] = $request->input('shipping_address_phone');
            $input1['sstate'] = $request->input('shipping_state');
            $input1['shippinggst'] = $request->input('shipping_gst');
            $input1['shippingpan'] = $request->input('shipping_pan');

            $this->repository->createCustomer($input1);

            $products = $request->input('new_product_obj');

            foreach ($products as $productLine) {
                if (!empty($productLine['product_name'])) {
                    $this->repository->createInvoiceProduct([
                        'invoice_id' => $id,
                        'product_name' => $productLine['product_name'],
                        'hsn' => $productLine['hsn'] ?? null,
                        'unit' => $productLine['unit'] ?? null,
                        'rate' => $productLine['product_rate'] ?? null,
                        'quantity' => $productLine['product_qty'] ?? null,
                        'total' => $productLine['product_price'] ?? null,
                        'gst' => $productLine['gst'] ?? null,
                        'gstamount' => $productLine['withtax'] ?? null,
                        'totalamount' => $productLine['total'] ?? null,
                    ]);
                }
            }
        });
    }

    public function getInvoiceDetails($id): array
    {
        $customer = $this->repository->getCustomersByInvoiceId($id);
        $state = $customer[0]->state;
        $invoice = $this->repository->getInvoiceRecords($id);
        $totalamountwithtax = $invoice[0]->amountwithtax;
        $amount = $invoice[0]->amount;
        $roundof = round($amount);
        $sgstamount = 0;
        $cgstamount = 0;
        $igsamount = 0;

        $invoiceproduct = $this->repository->getInvoiceProductsWithProductName($id);
        $totalGstAmount = (float) $invoiceproduct->sum('gstamount');

        if ($state === IndianStates::HOME_STATE) {
            $sgstamount = $totalGstAmount / 2;
            $cgstamount = $totalGstAmount / 2;
        } else {
            $igsamount = $totalGstAmount;
        }

        return compact('roundof', 'sgstamount', 'cgstamount', 'state', 'invoice', 'customer', 'invoiceproduct', 'totalamountwithtax', 'amount', 'igsamount');
    }

    public function recordPayment(array $data): bool
    {
        $itemId = $data['id'];
        $customerId = $data['customer_id'];
        $paidAmount = $data['paidAmount'];

        $item = $this->repository->findInvoice($itemId);

        $this->repository->createPaidAmount([
            'invoice_id' => $itemId,
            'customer_id' => $customerId,
            'paidAmount' => $paidAmount,
            'payment_method' => $data['payment_method'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
        ]);

        if ($item) {
            $newPaidAmount = $item->paidamount + $paidAmount;
            $remainingAmount = $item->amount - $newPaidAmount;

            $item->paidamount = $newPaidAmount;
            $item->remaining_amount = $remainingAmount;

            $this->repository->saveInvoice($item);

            return true;
        }

        return false;
    }

    public function getPaymentHistory(): Collection
    {
        return $this->repository->getPaymentHistory();
    }
}
