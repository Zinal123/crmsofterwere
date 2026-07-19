# Pan-India GST Engine Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the hardcoded-18%/exact-string-"Gujarat" tax calculation with a correct
sum-of-actual-line-amounts split, driven by a real state dropdown and a per-line editable GST
rate.

**Architecture:** No new domain, no new tables. Three additive/corrective changes inside the
existing Invoice domain: (1) a new `App\Support\IndianStates` helper, same shape as the existing
`App\Support\IndianNumber`; (2) a correctness fix to `InvoiceService::getInvoiceDetails()` plus
new validation in `InvoiceController::store()`; (3) Blade/JS changes to
`apps-invoices-create.blade.php` and `invoicecreate.init.js` to replace free-text state inputs
with dropdowns and the readonly GST field with an editable per-line select.

**Tech Stack:** Laravel 10, Blade, vanilla JS (no framework) in `invoicecreate.init.js`, PHPUnit
feature tests with `RefreshDatabase`.

## Global Constraints

- Company home state is **Gujarat** — this is a confirmed fact, not configurable. Define it once
  as `App\Support\IndianStates::HOME_STATE`, never re-type the literal `"Gujarat"` string
  elsewhere.
- GST rate stays **invoice-line-level only** — no `gst_rate` column is added to the `product`
  table. Do not touch `app/Models/Product.php` or any product migration.
- Existing historical invoice records (already-stored free-text states, already-stored flat-18%
  line amounts) are **not backfilled or corrected** — this plan fixes the input/calculation path
  going forward only.
- The existing `id` attributes on `billingstate`/`shippingstate`/`gst-N` elements **must not
  change** — surrounding JS reads/writes them by these exact ids (`document.getElementById(...)`)
  in multiple places not touched by this plan (edit-invoice load, same-as-billing checkbox). A
  `<select>` element with the same `id` and a `.value` property behaves identically to the
  `<input>` it replaces for every one of those call sites.
- Follow this app's existing `Controller → Service → Repository` layering
  (`docs/DEVELOPMENT-STANDARDS.md` section 1) — do not add Eloquent calls to
  `InvoiceController`.

---

### Task 1: `App\Support\IndianStates` helper

**Files:**
- Create: `app/Support/IndianStates.php`
- Test: `tests/Unit/Support/IndianStatesTest.php`

**Interfaces:**
- Produces: `App\Support\IndianStates::LIST` (array of 36 strings: 28 states + 8 union
  territories), `App\Support\IndianStates::HOME_STATE` (string constant, value `'Gujarat'`).
  Task 2 and Task 3 both consume these.

- [ ] **Step 1: Write the failing test**

```php
// tests/Unit/Support/IndianStatesTest.php
<?php

namespace Tests\Unit\Support;

use App\Support\IndianStates;
use PHPUnit\Framework\TestCase;

class IndianStatesTest extends TestCase
{
    public function test_list_contains_36_states_and_union_territories(): void
    {
        $this->assertCount(36, IndianStates::LIST);
    }

    public function test_list_contains_gujarat_and_maharashtra(): void
    {
        $this->assertContains('Gujarat', IndianStates::LIST);
        $this->assertContains('Maharashtra', IndianStates::LIST);
    }

    public function test_list_has_no_duplicates(): void
    {
        $this->assertCount(count(IndianStates::LIST), array_unique(IndianStates::LIST));
    }

    public function test_home_state_is_gujarat(): void
    {
        $this->assertSame('Gujarat', IndianStates::HOME_STATE);
    }

    public function test_home_state_is_itself_in_the_list(): void
    {
        $this->assertContains(IndianStates::HOME_STATE, IndianStates::LIST);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Support/IndianStatesTest.php`
Expected: FAIL with "Class App\Support\IndianStates not found"

- [ ] **Step 3: Write the implementation**

```php
// app/Support/IndianStates.php
<?php

namespace App\Support;

/**
 * The canonical list of India's 28 states + 8 union territories, used to
 * populate the billing/shipping state dropdowns on invoice creation and to
 * validate submitted state values server-side. HOME_STATE is the single
 * source of truth for "is this an intra-state (SGST+CGST) or inter-state
 * (IGST) sale?" in InvoiceService::getInvoiceDetails().
 */
class IndianStates
{
    public const HOME_STATE = 'Gujarat';

    public const LIST = [
        'Andhra Pradesh',
        'Arunachal Pradesh',
        'Assam',
        'Bihar',
        'Chhattisgarh',
        'Goa',
        'Gujarat',
        'Haryana',
        'Himachal Pradesh',
        'Jharkhand',
        'Karnataka',
        'Kerala',
        'Madhya Pradesh',
        'Maharashtra',
        'Manipur',
        'Meghalaya',
        'Mizoram',
        'Nagaland',
        'Odisha',
        'Punjab',
        'Rajasthan',
        'Sikkim',
        'Tamil Nadu',
        'Telangana',
        'Tripura',
        'Uttar Pradesh',
        'Uttarakhand',
        'West Bengal',
        'Andaman and Nicobar Islands',
        'Chandigarh',
        'Dadra and Nagar Haveli and Daman and Diu',
        'Delhi',
        'Jammu and Kashmir',
        'Ladakh',
        'Lakshadweep',
        'Puducherry',
    ];
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Support/IndianStatesTest.php`
Expected: PASS (5/5)

- [ ] **Step 5: Commit**

```bash
git add app/Support/IndianStates.php tests/Unit/Support/IndianStatesTest.php
git commit -m "feat(gst): add IndianStates helper (canonical state list + home state constant)"
```

---

### Task 2: Backend correctness fix — sum-based tax split + state validation

**Files:**
- Modify: `app/Services/Invoice/InvoiceService.php` (`getInvoiceDetails` method)
- Modify: `app/Http/Controllers/Invoice/InvoiceController.php` (`store` method's validation)
- Modify: `tests/Feature/InvoiceTest.php` (replace 2 existing tests that assert the old flat-18%
  behavior, add 2 new tests)

**Interfaces:**
- Consumes: `App\Support\IndianStates::LIST`, `App\Support\IndianStates::HOME_STATE` (Task 1).
- Produces: no new public methods — `getInvoiceDetails($id): array` keeps its existing signature
  and return shape (`sgstamount`, `cgstamount`, `igsamount` keys unchanged), only the *values* are
  now correct. Task 3 does not depend on anything from this task.

**Context:** `getInvoiceDetails()` currently computes `$sgstamount`/`$cgstamount`/`$igsamount` by
taking `$totalamountwithtax * 0.09` (or `* 0.18` for IGST) — a flat recompute that ignores the
per-line `gstamount` values already stored in the `invoiceproduct` table at creation time. Once
GST rate becomes variable per line (Task 3), this flat recompute would be actively wrong. The fix
sums the real per-line amounts instead. The repository call that fetches those line items
(`getInvoiceProductsWithProductName`) already happens later in this same method — this task only
reorders it earlier and sums its `gstamount` column.

- [ ] **Step 1: Write the failing tests — replace the two existing flat-18% tests**

Open `tests/Feature/InvoiceTest.php`. Replace these two existing tests (currently around lines
152-178):

```php
public function test_invoice_details_calculates_sgst_cgst_for_gujarat(): void
{
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000]);
    Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);

    $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

    $response->assertOk();
    $response->assertViewHas('sgstamount', 118000 * 0.09);
    $response->assertViewHas('cgstamount', 118000 * 0.09);
    $response->assertViewHas('igsamount', 0);
}

public function test_invoice_details_calculates_igst_for_non_gujarat(): void
{
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000]);
    Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Maharashtra']);

    $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

    $response->assertOk();
    $response->assertViewHas('igsamount', 118000 * 0.18);
    $response->assertViewHas('sgstamount', 0);
    $response->assertViewHas('cgstamount', 0);
}
```

with these four tests (the two above, corrected to sum-based math, plus two new ones):

```php
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
```

Also update the existing `test_invoicestore_creates_invoice_customer_and_products` test (around
line 102) to add `'shipping_state' => 'Gujarat',` alongside its existing `'billing_state' =>
'Gujarat',` line — Step 3 below makes `shipping_state` a required, validated field, so this
already-passing test needs it present to keep passing.

Add this import near the top of the file, alongside the existing `use App\Models\Invoiceproduct;`
if not already present (check first — it's already imported, listed alongside `Bank`/`Customer`/
`Invoice`/`Product`/`User` at the top of the file):

```php
use App\Models\Invoiceproduct;
```

- [ ] **Step 2: Run tests to verify the new/changed ones fail**

Run: `php artisan test tests/Feature/InvoiceTest.php`
Expected: the 3 sgst/cgst/igst tests FAIL (still using the old flat-18% math), the new
`test_invoicestore_rejects_a_state_not_in_the_indian_states_list` FAILs with a 200/no-validation
response instead of 422.

- [ ] **Step 3: Implement — add state validation to `InvoiceController::store()`**

In `app/Http/Controllers/Invoice/InvoiceController.php`, find:

```php
    public function store(Request $request)
    {
        $request->validate(['placesupply' => 'required|string|max:255']);

        $this->service->createInvoiceWithDetails($request);
```

Replace with:

```php
    public function store(Request $request)
    {
        $request->validate([
            'placesupply' => 'required|string|max:255',
            'billing_state' => ['required', 'string', \Illuminate\Validation\Rule::in(\App\Support\IndianStates::LIST)],
            'shipping_state' => ['required', 'string', \Illuminate\Validation\Rule::in(\App\Support\IndianStates::LIST)],
        ]);

        $this->service->createInvoiceWithDetails($request);
```

- [ ] **Step 4: Implement — fix `InvoiceService::getInvoiceDetails()`**

In `app/Services/Invoice/InvoiceService.php`, add this import alongside the existing ones at the
top of the file:

```php
use App\Support\IndianStates;
```

Then replace the entire `getInvoiceDetails` method:

```php
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

        if ($state == "Gujarat") {
            $sgstamount = $totalamountwithtax * 0.09;
            $cgstamount = $totalamountwithtax * 0.09;
        } else {
            $igsamount = $totalamountwithtax * 0.18;
        }

        $invoiceproduct = $this->repository->getInvoiceProductsWithProductName($id);

        return compact('roundof', 'sgstamount', 'cgstamount', 'state', 'invoice', 'customer', 'invoiceproduct', 'totalamountwithtax', 'amount', 'igsamount');
    }
```

with:

```php
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
```

Note: this moves the `getInvoiceProductsWithProductName` repository call earlier in the method
(it already existed later in the original method — no new repository method, no new query).

- [ ] **Step 5: Run tests to verify all pass**

Run: `php artisan test tests/Feature/InvoiceTest.php`
Expected: PASS, all tests including the 4 GST-related ones and the new validation test.

- [ ] **Step 6: Run the full Invoice-adjacent suite to confirm no regressions**

Run: `php artisan test --filter=Invoice`
Expected: all pass (this includes `InvoiceTest`, `UiComponents/ConfirmModalTest`'s invoice case,
`UiComponents/CreateButtonConsistencyTest`'s invoice case — same set the module already had).

- [ ] **Step 7: Commit**

```bash
git add app/Services/Invoice/InvoiceService.php app/Http/Controllers/Invoice/InvoiceController.php tests/Feature/InvoiceTest.php
git commit -m "fix(gst): compute SGST/CGST/IGST from actual per-line amounts instead of a flat 18% recompute; validate state against IndianStates"
```

---

### Task 3: Invoice creation UI — state dropdowns + per-line GST select

**Files:**
- Modify: `app/Services/Invoice/InvoiceService.php` (`getCreateViewData` method — add `states` key)
- Modify: `resources/views/apps-invoices-create.blade.php` (billing/shipping state inputs, the
  static first-row GST field)
- Modify: `public/build/js/pages/invoicecreate.init.js` (the dynamically-added row template's GST
  field, `updateQuantity()`'s hardcoded rate)
- Modify: `tests/Feature/InvoiceTest.php` (extend `test_create_page_renders_with_products_and_banks`)

**Interfaces:**
- Consumes: `App\Support\IndianStates::LIST` (Task 1). Does not depend on Task 2 — this task's
  changes are independently testable (the `<select>` markup renders correctly and the JS reads a
  row's own rate) without needing the backend calculation fix to also be in place.

**Context:** This task does NOT change the `id`s of `billingstate`/`shippingstate`/`gst-N`
elements or any of the field names in the AJAX payload (`billing_state`, `shipping_state`, `gst`
inside `new_product_obj`) — only the element type (`<input>` → `<select>`) and, for GST, the
`readonly` attribute and the hardcoded rate used in one JS calculation.

- [ ] **Step 1: Write the failing test**

In `tests/Feature/InvoiceTest.php`, find `test_create_page_renders_with_products_and_banks` and
extend it (add these two assertions to the existing test — don't create a new test method, this
is the page-render test that already covers this view):

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=test_create_page_renders_with_products_and_banks`
Expected: FAIL — no `<option>` elements exist yet, `gst-1` is still a readonly text input.

- [ ] **Step 3: Implement — pass states to the create view**

In `app/Services/Invoice/InvoiceService.php`, find:

```php
    public function getCreateViewData(): array
    {
        return [
            'product' => $this->productRepository->allOrderedByLatest(),
            'bank' => $this->bankRepository->all(),
        ];
    }
```

Replace with:

```php
    public function getCreateViewData(): array
    {
        return [
            'product' => $this->productRepository->allOrderedByLatest(),
            'bank' => $this->bankRepository->all(),
            'states' => IndianStates::LIST,
        ];
    }
```

(This reuses the `use App\Support\IndianStates;` import already added in Task 2, Step 4 — if
Task 3 is being done independently of Task 2 having landed, add that import now instead.)

- [ ] **Step 4: Implement — billing/shipping state dropdowns**

In `resources/views/apps-invoices-create.blade.php`, find the billing state field:

```blade
                            <div class="mb-3">
                                <input type="text" class="form-control bg-light border-0" id="billingstate" placeholder="State" required />
                                <div class="invalid-feedback">
                                    Please enter a State
                                </div>
                            </div>
```

Replace with:

```blade
                            <div class="mb-3">
                                <select class="form-select bg-light border-0" id="billingstate" required>
                                    <option value="">Select State</option>
                                    @foreach($states as $stateOption)
                                        <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Please select a State
                                </div>
                            </div>
```

Then find the shipping state field:

```blade
                                    <div class="mb-2">
                                        <input type="text" class="form-control bg-light border-0" id="shippingstate" placeholder="State" required />
                                        <div class="invalid-feedback">
                                            Please enter a State
                                        </div>
                                    </div>
```

Replace with:

```blade
                                    <div class="mb-2">
                                        <select class="form-select bg-light border-0" id="shippingstate" required>
                                            <option value="">Select State</option>
                                            @foreach($states as $stateOption)
                                                <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a State
                                        </div>
                                    </div>
```

- [ ] **Step 5: Implement — static first-row GST select**

In the same file, find:

```blade
                                    <td>
                                        <input type="text" class="form-control  bg-light border-0 gst" id="gst-1" step="0.01" placeholder="0.00"  readonly="readonly" value = "18" />
                                        
                                    </td>
```

Replace with:

```blade
                                    <td>
                                        <select class="form-select bg-light border-0 gst" id="gst-1">
                                            <option value="0">0%</option>
                                            <option value="5">5%</option>
                                            <option value="12">12%</option>
                                            <option value="18" selected>18%</option>
                                            <option value="28">28%</option>
                                        </select>
                                    </td>
```

- [ ] **Step 6: Run test to verify markup assertions pass**

Run: `php artisan test --filter=test_create_page_renders_with_products_and_banks`
Expected: PASS — the state-option and `gst-1` assertions now pass. (The dynamic-row-template and
`updateQuantity()` JS changes in the next two steps aren't visible to this Blade-render test;
they're covered by manual verification in Step 9.)

- [ ] **Step 7: Implement — dynamic row template's GST field**

In `public/build/js/pages/invoicecreate.init.js`, find (inside the row-template string
concatenation that builds each newly-added product row):

```javascript
        '<td class="text-end">' +
        "<div>" +
        '<input type="text" class="form-control bg-light border-0 gst" id="gst-' + count + '"  placeholder="$0.00" value ="18" >' +
        "</div>" +
        "</td>" +
```

Replace with:

```javascript
        '<td class="text-end">' +
        "<div>" +
        '<select class="form-select bg-light border-0 gst" id="gst-' + count + '">' +
        '<option value="0">0%</option>' +
        '<option value="5">5%</option>' +
        '<option value="12">12%</option>' +
        '<option value="18" selected>18%</option>' +
        '<option value="28">28%</option>' +
        "</select>" +
        "</div>" +
        "</td>" +
```

- [ ] **Step 8: Implement — `updateQuantity()` reads the row's own selected rate**

In the same file, find:

```javascript
function updateQuantity(amount, itemQuntity, priceselection) {
    var linePrice = amount * itemQuntity;
    linePrice = linePrice.toFixed(2);
    priceselection.value = linePrice;
    taxableamount = linePrice * 0.18;
    taxableamount1 = taxableamount.toFixed(2);
    taxableamount2 = paymentSign + taxableamount1;
    const num1 = parseInt(linePrice);
    const num2 = parseInt(taxableamount);
    taxableamount4 = num1 + num2;
    taxableamount5 = taxableamount4.toFixed(2);
    document.getElementById('withtax-' + count + '').value = (taxableamount1);
    document.getElementById('total-' + count + '').value = (taxableamount5);
    recalculateCart();
}
```

Replace with:

```javascript
function updateQuantity(amount, itemQuntity, priceselection) {
    var linePrice = amount * itemQuntity;
    linePrice = linePrice.toFixed(2);
    priceselection.value = linePrice;
    var gstSelect = document.getElementById('gst-' + count + '');
    var lineGstRate = gstSelect ? (parseFloat(gstSelect.value) / 100) : 0.18;
    taxableamount = linePrice * lineGstRate;
    taxableamount1 = taxableamount.toFixed(2);
    taxableamount2 = paymentSign + taxableamount1;
    const num1 = parseInt(linePrice);
    const num2 = parseInt(taxableamount);
    taxableamount4 = num1 + num2;
    taxableamount5 = taxableamount4.toFixed(2);
    document.getElementById('withtax-' + count + '').value = (taxableamount1);
    document.getElementById('total-' + count + '').value = (taxableamount5);
    recalculateCart();
}
```

Note: this preserves the pre-existing behavior of targeting the global `count`-indexed row rather
than the specific row that triggered the event — that's an existing latent quirk unrelated to
GST, out of scope for this fix (document, don't drive-by-fix). The `gstSelect ? ... : 0.18`
fallback exists only so a missing element doesn't throw; in normal operation the select always
exists for every row index up to `count`.

Also note: the unused `var taxRate = 0.18;` declared earlier in this file (near
`var shippingRate = 65.0;`) has no other read site in the file (confirmed via grep) — leave it
alone, it's pre-existing dead code, not part of this fix's scope.

- [ ] **Step 9: Manual verification (no automated test covers dynamically-added rows or live JS calculation — this app has no JS test runner)**

In a browser, open the invoice creation page:
1. Click "Add Item" to add a second product row — confirm the new row's GST field is a `<select>`
   with options 0/5/12/18/28%, defaulting to 18%, not a readonly text field.
2. Change a row's GST% to 5%, enter a rate and quantity — confirm that row's "GST Amount" (withtax)
   column reflects 5% of the line price, not 18%.
3. Confirm the billing and shipping State fields are dropdowns listing real Indian states, and the
   "same as billing" checkbox still correctly copies the selected state to shipping.
4. Submit an invoice with a Gujarat billing state and view its details page — confirm the
   SGST/CGST amounts shown match half of that invoice's actual total GST amount (visible in the
   line items), not a flat 18%-of-total.

- [ ] **Step 10: Run the full Invoice suite one more time**

Run: `php artisan test --filter=Invoice`
Expected: all pass.

- [ ] **Step 11: Commit**

```bash
git add app/Services/Invoice/InvoiceService.php resources/views/apps-invoices-create.blade.php public/build/js/pages/invoicecreate.init.js tests/Feature/InvoiceTest.php
git commit -m "feat(gst): replace free-text state inputs with dropdowns, make per-line GST rate editable (0/5/12/18/28%)"
```

---

## Final Verification

- [ ] Run the complete app test suite: `php artisan test` — expect the same or higher pass count
  than the pre-plan baseline (161 passing as of the last confirmed full-suite run), 0 failures.
- [ ] Manual authenticated smoke test against the real live MySQL DB (per this project's standing
  sqlite-vs-MySQL testing gap convention — the automated suite above only proves correctness
  against sqlite): create one real invoice with a Gujarat customer and mixed-rate line items,
  view its details page, confirm SGST/CGST math by hand.
