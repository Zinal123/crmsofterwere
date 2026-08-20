<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_renders_with_products_and_banks(): void
    {
        $user = User::factory()->create();
        // Fixed 2026-07-19: the product dropdown previously rendered
        // $p->product, but Product has no `product` column (only `name`) -
        // so the option text was always blank. Now uses $p->name.
        Product::factory()->create(['name' => 'Fiber Laser Cutting Machine']);
        Bank::create(['bankholdername' => 'Oracle Machine Tech', 'bankname' => 'HDFC Bank']);

        $response = $this->actingAs($user)->get(route('invoice.create'));

        $response->assertOk();
        $response->assertSee('Fiber Laser Cutting Machine');
        $response->assertSee('HDFC Bank');
        // Pan-India GST Engine: state dropdown replaces free-text input,
        // per-line GST select replaces the readonly-18-only field.
        $response->assertSee('<option value="Gujarat">Gujarat</option>', false);
        $response->assertSee('<option value="Maharashtra">Maharashtra</option>', false);
        $response->assertSee('id="gst-1"', false);
        $response->assertDontSee('readonly="readonly" value = "18"', false);
    }

    public function test_create_page_product_line_delete_button_is_styled_as_destructive(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.create'));

        $response->assertOk();
        // Was btn-success (green) - misleadingly styled as a positive/safe
        // action for something that removes a line item.
        $response->assertDontSee('class="btn btn-success">Delete</a>', false);
        $response->assertSee('class="btn btn-danger">Delete</a>', false);
    }

    public function test_getproduct_returns_product_list_json(): void
    {
        $user = User::factory()->create();
        Product::factory()->create(['name' => 'CO2 Laser Cutting Machine']);

        $response = $this->actingAs($user)->get(route('invoice.product'));

        $response->assertOk();
        $response->assertJson(['isSuccess' => true]);
        $response->assertJsonFragment(['name' => 'CO2 Laser Cutting Machine']);
    }

    public function test_getproductvalue1_returns_pricing_for_a_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['make' => 'FLC-1500', 'rate' => 850000, 'unit' => 'Nos']);

        $response = $this->actingAs($user)->get(route('invoice.product1', ['paymentType1' => $product->id]));

        $response->assertOk();
        $response->assertJson([
            'isSuccess' => true,
            'make' => 'FLC-1500',
            'unit' => 'Nos',
            'rate' => '850000',
        ]);
    }

    public function test_datatable_endpoint_returns_invoice_rows(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'paidamount' => 100000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'name' => 'Rajasthan Metal Works']);

        $response = $this->actingAs($user)->get(route('invoice.data'));

        $response->assertOk();
        $response->assertJsonFragment(['recordsTotal' => 1]);
        $response->assertSee('Rajasthan Metal Works');
        $response->assertSee('Paid');
    }

    public function test_datatable_endpoint_filters_by_date_range_when_provided(): void
    {
        $user = User::factory()->create();
        $inRange = Invoice::factory()->create(['date' => '2026-07-15']);
        Customer::factory()->create(['invoice_id' => $inRange->id, 'name' => 'In Range Buyer']);
        $outOfRange = Invoice::factory()->create(['date' => '2026-06-15']);
        Customer::factory()->create(['invoice_id' => $outOfRange->id, 'name' => 'Out Of Range Buyer']);

        $response = $this->actingAs($user)->get(route('invoice.data', ['from' => '2026-07-01', 'to' => '2026-07-31']));

        $response->assertOk();
        // recordsTotal stays the true unfiltered count (DataTables
        // convention) - only recordsFiltered reflects the date range.
        $response->assertJsonFragment(['recordsTotal' => 2, 'recordsFiltered' => 1]);
        $response->assertSee('In Range Buyer');
        $response->assertDontSee('Out Of Range Buyer');
    }

    public function test_datatable_endpoint_returns_every_invoice_when_no_date_range_given(): void
    {
        // Existing behaviour must not regress: with no from/to, every invoice
        // still shows up, exactly as before this filter was added.
        $user = User::factory()->create();
        $one = Invoice::factory()->create(['date' => '2026-07-15']);
        Customer::factory()->create(['invoice_id' => $one->id]);
        $two = Invoice::factory()->create(['date' => '2020-01-01']);
        Customer::factory()->create(['invoice_id' => $two->id]);

        $response = $this->actingAs($user)->get(route('invoice.data'));

        $response->assertOk();
        $response->assertJsonFragment(['recordsTotal' => 2, 'recordsFiltered' => 2]);
    }

    public function test_datatable_status_badge_uses_icon_and_correct_color(): void
    {
        $user = \App\Models\User::factory()->create();
        $invoice = \App\Models\Invoice::factory()->create(['amount' => 100000, 'paidamount' => 100000]);
        \App\Models\Customer::factory()->create(['invoice_id' => $invoice->id]);
        $pendingInvoice = \App\Models\Invoice::factory()->create(['amount' => 50000, 'paidamount' => 0]);
        \App\Models\Customer::factory()->create(['invoice_id' => $pendingInvoice->id]);

        $response = $this->actingAs($user)->get(route('invoice.data'));

        $response->assertOk();
        $response->assertSee('ri-checkbox-circle-line', false);
        $response->assertSee('ri-time-line', false);
    }

    public function test_invoice_list_page_reflects_a_date_range_from_the_url_into_its_filter_and_ajax_call(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice', ['from' => '2026-07-01', 'to' => '2026-07-31']));

        $response->assertOk();
        $response->assertSee('value="2026-07-01"', false);
        $response->assertSee('value="2026-07-31"', false);
        $response->assertSee('d.from = "2026-07-01"', false);
        $response->assertSee('d.to = "2026-07-31"', false);
    }

    public function test_invoice_list_datatable_has_processing_and_language_config(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice'));

        $response->assertOk();
        $response->assertSee('processing: true', false);
        $response->assertSee('emptyTable', false);
    }

    public function test_invoicestore_creates_invoice_customer_and_products(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(route('invoice.store'), [
            'invoice_id' => 'INV-TEST-001',
            'invoice_date' => '2026-07-17',
            'placesupply' => 'Gujarat',
            'billing_address_full_name' => 'Test Customer',
            'billing_state' => 'Gujarat',
            'shipping_state' => 'Gujarat',
            'order_summary_cart_total' => 50000,
            'order_summary_cart_amount' => 50000,
            'new_product_obj' => [
                [
                    'product_name' => $product->id,
                    'hsn' => '8456',
                    'unit' => 'Nos',
                    'product_rate' => 50000,
                    'product_qty' => 1,
                    'product_price' => 50000,
                    'gst' => 18,
                    'withtax' => 9000,
                    'total' => 59000,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['isSuccess' => true]);
        $this->assertDatabaseHas('invoice', ['invoice_id' => 'INV-TEST-001']);
        $this->assertDatabaseHas('customer', ['name' => 'Test Customer']);
        $this->assertDatabaseHas('invoiceproduct', ['product_name' => $product->id, 'hsn' => '8456']);
        // Regression: remaining_amount previously stayed NULL until the first
        // payment was recorded, which hid the "Record Payment" form (gated on
        // remaining_amount > 0) on a brand-new, unpaid invoice.
        $this->assertDatabaseHas('invoice', ['invoice_id' => 'INV-TEST-001', 'paidamount' => 0, 'remaining_amount' => 50000]);
    }

    public function test_a_freshly_created_invoice_shows_the_record_payment_button(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post(route('invoice.store'), [
            'invoice_id' => 'INV-TEST-005',
            'invoice_date' => '2026-07-17',
            'placesupply' => 'Gujarat',
            'billing_address_full_name' => 'Fresh Invoice Customer',
            'billing_state' => 'Gujarat',
            'shipping_state' => 'Gujarat',
            'order_summary_cart_total' => 50000,
            'order_summary_cart_amount' => 50000,
            'new_product_obj' => [
                ['product_name' => $product->id],
            ],
        ]);

        $invoice = Invoice::where('invoice_id', 'INV-TEST-005')->firstOrFail();

        $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertSee('id="invoice-payment-form"', false);
    }

    public function test_invoicestore_accepts_a_product_line_missing_optional_fields(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Only product_name is present - hsn/unit/product_rate/product_qty/product_price/gst/withtax/total
        // are all missing, which previously threw an unhandled "Undefined array key" 500
        // instead of just leaving those columns null (all nullable in the schema).
        $response = $this->actingAs($user)->postJson(route('invoice.store'), [
            'invoice_id' => 'INV-TEST-004',
            'placesupply' => 'Gujarat',
            'billing_state' => 'Gujarat',
            'shipping_state' => 'Gujarat',
            'order_summary_cart_total' => 50000,
            'order_summary_cart_amount' => 50000,
            'new_product_obj' => [
                [
                    'product_name' => $product->id,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['isSuccess' => true]);
        $this->assertDatabaseHas('invoiceproduct', ['product_name' => $product->id, 'hsn' => null]);
    }

    public function test_invoicestore_fails_validation_without_placesupply(): void
    {
        $user = User::factory()->create();

        // postJson to match how the real frontend calls this (jQuery $.ajax
        // with dataType: 'json'), which is what makes Laravel return 422
        // JSON instead of a redirect-back-with-errors.
        $response = $this->actingAs($user)->postJson(route('invoice.store'), [
            'invoice_id' => 'INV-TEST-002',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('invoice', ['invoice_id' => 'INV-TEST-002']);
    }

    public function test_invoice_details_calculates_sgst_cgst_for_gujarat(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);
        Invoiceproduct::factory()->create(['invoice_id' => $invoice->id, 'gst' => 18, 'gstamount' => 18000]);

        $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertViewHas('sgstamount', 9000.0);
        $response->assertViewHas('cgstamount', 9000.0);
        $response->assertViewHas('igsamount', 0);
    }

    public function test_invoice_details_calculates_igst_for_non_gujarat(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Maharashtra']);
        Invoiceproduct::factory()->create(['invoice_id' => $invoice->id, 'gst' => 18, 'gstamount' => 18000]);

        $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertViewHas('igsamount', 18000.0);
        $response->assertViewHas('sgstamount', 0);
        $response->assertViewHas('cgstamount', 0);
    }

    public function test_invoice_details_shows_the_real_invoice_number_not_a_hardcoded_placeholder(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['invoice_id' => 'INV-2026-0042']);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);
        Invoiceproduct::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertSee('INV-2026-0042');
        $response->assertDontSee('GC-24');
    }

    public function test_invoice_details_sums_mixed_gst_rates_across_line_items(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 123000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);
        Invoiceproduct::factory()->create(['invoice_id' => $invoice->id, 'gst' => 5, 'gstamount' => 5000]);
        Invoiceproduct::factory()->create(['invoice_id' => $invoice->id, 'gst' => 18, 'gstamount' => 18000]);

        $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        // 5000 + 18000 = 23000 total GST, split 50/50 for an intra-state (Gujarat) sale.
        $response->assertViewHas('sgstamount', 11500.0);
        $response->assertViewHas('cgstamount', 11500.0);
        $response->assertViewHas('igsamount', 0);
    }

    public function test_invoicestore_rejects_a_state_not_in_the_indian_states_list(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->postJson(route('invoice.store'), [
            'invoice_id' => 'INV-TEST-003',
            'placesupply' => 'Gujarat',
            'billing_state' => 'Not A Real State',
            'shipping_state' => 'Gujarat',
            'order_summary_cart_total' => 50000,
            'order_summary_cart_amount' => 50000,
            'new_product_obj' => [
                [
                    'product_name' => $product->id,
                    'gst' => 18,
                    'withtax' => 9000,
                    'total' => 59000,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('invoice', ['invoice_id' => 'INV-TEST-003']);
    }

    public function test_updatepayment_updates_paid_and_remaining_amount(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'paidamount' => 0, 'remaining_amount' => 100000]);
        $customer = Customer::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)->post('/update-payment', [
            'id' => $invoice->id,
            'customer_id' => $customer->id,
            'paidAmount' => 40000,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'TXN12345',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertEquals(40000, $invoice->fresh()->paidamount);
        $this->assertEquals(60000, $invoice->fresh()->remaining_amount);
        $this->assertDatabaseHas('paidamount', [
            'invoice_id' => $invoice->id,
            'paidAmount' => 40000,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'TXN12345',
        ]);
    }

    public function test_updatepayment_rejects_a_missing_payment_method(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'paidamount' => 0, 'remaining_amount' => 100000]);
        $customer = Customer::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)->postJson('/update-payment', [
            'id' => $invoice->id,
            'customer_id' => $customer->id,
            'paidAmount' => 5000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payment_method');
        $this->assertEquals(0, $invoice->fresh()->paidamount);
    }

    public function test_updatepayment_rejects_an_absurdly_large_amount(): void
    {
        // Regression test: a QA fuzz-test payload once slipped an
        // unbounded value (67978778979789) straight through with no
        // validation, corrupting a real invoice's paid/remaining totals.
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'paidamount' => 0, 'remaining_amount' => 100000]);
        $customer = Customer::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)->postJson('/update-payment', [
            'id' => $invoice->id,
            'customer_id' => $customer->id,
            'paidAmount' => 67978778979789,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, $invoice->fresh()->paidamount);
    }

    public function test_updatepayment_rejects_a_negative_amount(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'paidamount' => 0, 'remaining_amount' => 100000]);
        $customer = Customer::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)->postJson('/update-payment', [
            'id' => $invoice->id,
            'customer_id' => $customer->id,
            'paidAmount' => -500,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, $invoice->fresh()->paidamount);
    }

    public function test_updatepayment_rejects_a_payment_that_would_exceed_the_remaining_balance(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'paidamount' => 70000, 'remaining_amount' => 30000]);
        $customer = Customer::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)->postJson('/update-payment', [
            'id' => $invoice->id,
            'customer_id' => $customer->id,
            'paidAmount' => 30000.01,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('paidAmount');
        $this->assertEquals(70000, $invoice->fresh()->paidamount);
        $this->assertEquals(30000, $invoice->fresh()->remaining_amount);
        $this->assertDatabaseMissing('paidamount', ['invoice_id' => $invoice->id, 'paidAmount' => 30000.01]);
    }

    public function test_updatepayment_allows_a_payment_that_exactly_settles_the_remaining_balance(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'paidamount' => 70000, 'remaining_amount' => 30000]);
        $customer = Customer::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)->postJson('/update-payment', [
            'id' => $invoice->id,
            'customer_id' => $customer->id,
            'paidAmount' => 30000,
            'payment_method' => 'cash',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertEquals(100000, $invoice->fresh()->paidamount);
        $this->assertEquals(0, $invoice->fresh()->remaining_amount);
    }

    public function test_invoice_details_page_shows_payment_form_and_history_when_unpaid(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000, 'paidamount' => 40000, 'remaining_amount' => 60000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);
        \App\Models\Paidamount::create(['invoice_id' => $invoice->id, 'customer_id' => 1, 'paidAmount' => 40000]);

        $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertSee('id="invoice-payment-form"', false);
        $response->assertSee('Record Payment');
    }

    public function test_invoice_details_page_hides_payment_form_when_fully_paid(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000, 'paidamount' => 100000, 'remaining_amount' => 0]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);

        $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertDontSee('id="invoice-payment-form"', false);
    }

    public function test_recording_a_payment_from_the_invoice_details_page_updates_totals(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000, 'paidamount' => 0, 'remaining_amount' => 100000]);
        $customer = Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);

        $response = $this->actingAs($user)->postJson('/update-payment', [
            'id' => $invoice->id,
            'customer_id' => $customer->id,
            'paidAmount' => 30000,
            'payment_method' => 'upi',
            'reference_number' => 'UPI98765',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $detailsResponse = $this->actingAs($user)->get(route('invoice.details', $invoice->id));
        $detailsResponse->assertOk();
        $detailsResponse->assertSee(\App\Support\IndianNumber::format(30000), false);
        $detailsResponse->assertSee(\App\Support\IndianNumber::format(70000), false);
        $detailsResponse->assertSee('UPI');
        $detailsResponse->assertSee('UPI98765');
    }

    public function test_datatable_endpoint_escapes_html_in_customer_name_and_phone(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'paidamount' => 100000]);
        Customer::factory()->create([
            'invoice_id' => $invoice->id,
            'name' => '<script>alert(1)</script>',
            'phone' => '<img src=x onerror=alert(1)>',
        ]);

        $response = $this->actingAs($user)->get(route('invoice.data'));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
        $response->assertSee('&lt;script&gt;alert(1)&lt;\/script&gt;', false);
        $response->assertSee('&lt;img src=x onerror=alert(1)&gt;', false);
    }

    public function test_paymenthistry_page_renders_with_seeded_payments(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'paidamount' => 0, 'remaining_amount' => 100000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'name' => 'Precision CNC Works']);

        $this->actingAs($user)->post('/update-payment', [
            'id' => $invoice->id,
            'customer_id' => Customer::first()->id,
            'paidAmount' => 25000,
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($user)->get(route('invoice.histry'));

        $response->assertOk();
        $response->assertSee('Precision CNC Works');
    }

    public function test_paymenthistry_breadcrumb_says_payment_history_not_invoices(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.histry'));

        $response->assertOk();
        $response->assertSee('Payment History');
        $response->assertDontSee('list view');
    }

    public function test_paymenthistry_page_has_no_dead_unreachable_modal_markup(): void
    {
        // Neither modal on this page has ever had a trigger button in the
        // table - it's a read-only listing (id/customer/amount only, no
        // action column) - so both were 100% dead, copy-pasted leftovers.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.histry'));

        $response->assertOk();
        $response->assertDontSee('exampleModalgrid', false);
        $response->assertDontSee('deleteOrder', false);
    }

    public function test_owner_can_update_an_invoices_header_and_customer_details(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000, 'placesupply' => 'Gujarat']);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'name' => 'Old Customer Name', 'state' => 'Gujarat']);

        $response = $this->actingAs($user)->put(route('invoice.update', $invoice->id), [
            'name' => 'New Customer Name',
            'address' => 'New Address, Ahmedabad',
            'phone' => '9998887777',
            'email' => 'new@example.com',
            'state' => 'Maharashtra',
            'bankaccountnumber' => '1234567890',
            'bankifsccode' => 'HDFC0001234',
            'accountholder' => 'Oracle Machine Tech',
            'bankname' => 'HDFC Bank',
            'placesupply' => 'Maharashtra',
        ]);

        $response->assertRedirect(route('invoice.details', $invoice->id));
        $this->assertDatabaseHas('customer', ['invoice_id' => $invoice->id, 'name' => 'New Customer Name', 'state' => 'Maharashtra']);
        $this->assertDatabaseHas('invoice', ['id' => $invoice->id, 'placesupply' => 'Maharashtra', 'bankname' => 'HDFC Bank']);
    }

    public function test_owner_can_delete_an_invoice_and_its_related_records(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000]);
        Customer::factory()->create(['invoice_id' => $invoice->id]);
        Invoiceproduct::factory()->create(['invoice_id' => $invoice->id]);
        \App\Models\Paidamount::create(['invoice_id' => $invoice->id, 'customer_id' => 1, 'paidAmount' => 1000]);

        $response = $this->actingAs($user)->delete(route('invoice.destroy', $invoice->id));

        $response->assertRedirect(route('invoice'));
        $this->assertDatabaseMissing('invoice', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('customer', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('invoiceproduct', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('paidamount', ['invoice_id' => $invoice->id]);
    }

    public function test_invoice_details_page_has_edit_and_delete_triggers(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);

        $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertSee('data-bs-target="#editInvoice"', false);
        $response->assertSee(route('invoice.destroy', $invoice->id), false);
    }
}
