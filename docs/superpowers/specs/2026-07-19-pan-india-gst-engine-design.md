# Pan-India GST Engine — Design

## Context

`docs/Oracle-Machine-Tech-Build-Order.pdf` (the external Phase 1 audit) flags this app's GST
handling as effectively single-state. Confirmed in code:

1. `InvoiceService::getInvoiceDetails()` (`app/Services/Invoice/InvoiceService.php:190`)
   recomputes tax at display time using a **hardcoded flat 18%**, split by an **exact string
   match** `$state == "Gujarat"` — SGST+CGST if true, IGST otherwise. It ignores the per-line
   GST amounts already stored at invoice creation.
2. Customer billing/shipping state is a **free-text `<input>`**
   (`resources/views/apps-invoices-create.blade.php:190,241`, ids `billingstate`/
   `shippingstate`), so the one state check that exists is fragile — `"gujarat"`, `"Gujarat "`,
   or a typo like `"Gujrat"` all silently fall through to the IGST branch.
3. The per-line GST% field on invoice creation is `readonly`, hardcoded to `18` both in markup
   (`apps-invoices-create.blade.php:330`, `value="18"`) and JS
   (`public/build/js/pages/invoicecreate.init.js:211,250,369`, `taxRate = 0.18`) — every line
   item is taxed identically regardless of its actual HSN slab (India has 0/5/12/18/28% GST
   slabs depending on product classification).

Oracle Machine Tech's registered business state is confirmed to be Gujarat, so the *direction* of
the existing SGST+CGST-vs-IGST logic is correct — it's the implementation that's unsafe.

## Confirmed Decisions

- **Company home state is Gujarat** — the intra-state (SGST+CGST) vs inter-state (IGST) branch
  direction is correct as-is; the bug is in how "is this Gujarat?" is determined, not the tax
  logic itself.
- **GST rate becomes editable per invoice line item**, not locked to 18%. Default remains 18%,
  but the field becomes a real `<select>` of the standard slabs (0/5/12/18/28%) instead of a
  readonly text field.
- **No Product-catalog change.** GST rate is not stored per Product — it's set at
  invoice-creation time per line, same as today's rate/quantity fields. This can be revisited
  later if the catalog needs it; not needed now.
- **State input becomes a dropdown**, not free text with normalization. Both billing and shipping
  state fields are replaced with a `<select>` populated from the real list of India's 28 states +
  8 union territories. This eliminates the typo/case-mismatch bug class entirely and lets the
  backend validate the submitted value is a real state (`in:` rule) instead of accepting
  anything.

## Approach

Small, targeted fix — no new domain, no new tables. Touches the existing Invoice domain only.

### `App\Support\IndianStates`

A new static helper, same shape as the existing `App\Support\IndianNumber` (a small, dependency-
free `Support` class). Holds the canonical list of India's 28 states + 8 union territories as a
`const LIST = [...]` array, plus a `const HOME_STATE = 'Gujarat'` constant (replaces the inline
`"Gujarat"` literal currently hardcoded in `InvoiceService`). Two call sites reuse this: the
Blade `<select>` on invoice creation, and a Laravel validation rule (`Rule::in(IndianStates::LIST)`)
on the state fields when the invoice is stored.

### Invoice creation UI

`resources/views/apps-invoices-create.blade.php`:
- `billingstate`/`shippingstate` `<input type="text">` → `<select>`, options rendered from
  `App\Support\IndianStates::LIST`, keeping the existing `id`s so the surrounding JS (which reads
  `document.getElementById("billingstate").value`, `invoicecreate.init.js:406-408,483,584,589`)
  keeps working unchanged — a `<select>`'s `.value` behaves the same way a text input's does for
  every read site already in that file.
- The per-line GST% field (`apps-invoices-create.blade.php:330`, and the JS-generated row
  template at `invoicecreate.init.js:211`) → `<select>` with options `0`, `5`, `12`, `18`, `28`,
  defaulting to `18`, no longer `readonly`. `invoicecreate.init.js:250,369`'s
  `taxRate = 0.18` constant becomes a per-row read of that row's selected GST value instead of a
  single hardcoded constant, so line items can carry different rates in the same invoice.

### Backend validation

`InvoiceService`'s store path validates `billing_address.state`/`shipping_state` against
`IndianStates::LIST` (`Rule::in`) before the customer record is created — a submitted value that
isn't a real state/UT name is now a validation error, not a silent IGST fallback.

### `InvoiceService::getInvoiceDetails()` — the core fix

Replace the flat-18%-recompute with a **sum of the actual stored per-line amounts**:

1. Sum `invoiceproduct.gstamount` across all line items for the invoice (already computed and
   stored correctly per-line at creation time by the existing `invoicecreate.init.js` flow — this
   value doesn't need to be recalculated, just aggregated).
2. If `$customer->state === App\Support\IndianStates::HOME_STATE` (exact match against the now-
   dropdown-constrained, validated value — safe to compare directly, no normalization needed
   since the dropdown guarantees canonical casing): split that summed amount 50/50 into
   `$sgstamount`/`$cgstamount`.
3. Otherwise: the full summed amount goes to `$igsamount`.

This is a correctness fix, not new business logic — it's the direct consequence of the GST rate
becoming genuinely variable per line; a flat-18%-of-total recompute would be actively wrong once
different lines can carry different rates.

`resources/views/apps-invoices-details.blade.php` (the print/view page) needs **no markup
change** — it already just prints whatever `$sgstamount`/`$cgstamount`/`$igsamount` the service
returns (lines 223, 234, 245).

## Testing

Following this app's test-first-then-refactor convention — since the current behavior *is* the
bug, tests are written for the correct target behavior directly rather than characterizing the
broken state first:

- Gujarat customer, single line item → `sgstamount`/`cgstamount` each equal half the line's
  actual stored `gstamount`, `igsamount` is 0.
- Non-Gujarat state (e.g. Maharashtra) → full stored `gstamount` total goes to `igsamount`,
  `sgstamount`/`cgstamount` are 0.
- Two line items at different GST rates (e.g. one at 5%, one at 18%) on a Gujarat invoice →
  `sgstamount`+`cgstamount` correctly reflects the sum of both lines' actual amounts, not a flat
  18%-of-total.
- Submitting an invoice with a state value not in `IndianStates::LIST` fails validation.
- The state `<select>` renders all 36 entries; the per-line GST `<select>` renders the 5 standard
  slabs with 18 as the default-selected option.

## Out of Scope

- FY-scoped sequential document numbering (`invoice_id` is still free-text) — a separate,
  already-identified Phase 1 item, not touched here.
- Storing a default GST rate on the Product catalog — explicitly deferred; rate stays
  invoice-line-level only for now.
- Union Territory intra-UT GST rules (UTGST) — not applicable since the seller's home state
  (Gujarat) is a state, not a UT; every UT customer is correctly inter-state (IGST) under this
  design, same as any other non-Gujarat state.
- Backfilling/correcting historical invoices already stored with free-text (possibly
  typo'd/miscased) state values or a flat 18% line rate — this design fixes the input path going
  forward only; existing invoice records are untouched.
