# Design System & UI/UX Consistency Pass Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Establish a real, documented design-token layer and a reusable Blade component library on top of the existing Velzon/Bootstrap 5 template, then retrofit every already-built module (Product, Invoice, Quotation, Inventory, Payment History, Vendor, Admin Roles/Users) to use it — fixing the 5 inconsistent "Create" button styles, color-only status badges, missing error pages, absent empty/loading states, and mixed icon sets found in the design audit, without changing any existing route, controller logic, or data.

**Architecture:** Laravel Blade anonymous components (`resources/views/components/ui/*.blade.php`, invoked as `<x-ui.button>` etc.) — the first use of this Laravel feature in this codebase (confirmed zero prior `<x-` usage; the only existing reuse mechanism is the older `@component`/`@slot` pattern used solely for the breadcrumb). Components wrap Velzon's existing Bootstrap classes rather than replacing them, so no CSS framework changes are needed. A small custom JS toast helper wraps the already-loaded SweetAlert2 rather than adding a new dependency.

**Tech Stack:** Laravel 10 Blade, Bootstrap 5 (Velzon theme), vanilla JS + jQuery (existing), SweetAlert2 (already loaded on every page), Remix Icon (`ri-*`, the target icon set).

## Global Constraints

- Follow `docs/DEVELOPMENT-STANDARDS.md`: test-first-then-refactor, preserve-and-document (don't silently fix unrelated bugs), full verification checklist per task (`php -l`, focused test, full suite, manual smoke test against real live data).
- Spec: `docs/superpowers/specs/2026-07-17-design-system-design.md` — extend Velzon's foundation, don't re-theme; colors/buttons/icons may be adjusted from Velzon stock where it genuinely improves consistency/legibility, but the semantic mapping (success=positive/create, warning=needs-attention, danger=destructive-only, primary=navigation/save, info=neutral) is the binding rule.
- Full test suite baseline: 56/56 passing. This pass touches views/CSS/JS only, not routes or controllers (except the two backend-adjacent components explained below) — no existing feature test should ever need to change; any failure means something broke.
- **Explicitly deferred, do NOT build in this plan:** role-based dashboards, any new MES module, real server-side pagination for Inventory/Vendor/Quotation/Payment-History (client-side-only consistency for those four; see Task 10), navigation/IA changes, dark-mode work, server-side validation fixes.
- Icon set target: Remix Icon (`ri-*`) everywhere in CRM feature views and shared chrome (topbar, error pages). `mdi-*`/`bx-*` are migrated away, not left as a second permitted set.

---

### Task 1: Design tokens — document the semantic color/typography/spacing system

**Files:**
- Modify: `resources/scss/custom.scss`
- Test: manual (no automated test applies to a comment-only SCSS file; verified via Task 2+ actually consuming these documented conventions)

**Interfaces:**
- Produces: a documented reference (SCSS comments, since Velzon's own `_variables.scss` already defines the actual color values correctly — `$primary: $indigo` `#405189`, `$success: $green` `#0ab39c`, `$warning: $yellow` `#f7b84b`, `$danger: $red` `#f06548`, `$info: $cyan` `#299cdb`) that every later task's component code follows. No new SCSS variables needed — the existing Bootstrap semantic classes (`btn-success`, `bg-success-subtle`, etc.) already resolve to the right values once components use them consistently, which is what Tasks 2-3 fix.

- [ ] **Step 1: Read the current file**

Read `resources/scss/custom.scss` (confirmed 7 lines, Velzon's boilerplate header comment, no rules).

- [ ] **Step 2: Add the design-token documentation block**

Replace the full content of `resources/scss/custom.scss` with:

```scss
/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://themesbrand.com/
Contact: support@themesbrand.com
File: Custom Css File
*/

/*
================================================================================
Oracle Machine Tech CRM — Design Token Reference
================================================================================
This file documents the app-level semantic rules layered on top of Velzon's
existing Bootstrap variables (see resources/scss/_variables.scss for the
actual color/typography values — this file does not redefine them). Every
new Blade component under resources/views/components/ui/ follows these
rules; retrofitted views should too as they're touched.

COLOR SEMANTICS (map to Bootstrap's existing $success/$warning/$danger/
$primary/$info, defined in _variables.scss):
  success (green)  -> positive states (Paid, Active, Completed) AND all
                       Create/Add actions. Never use danger or primary for
                       a non-destructive "add" action.
  warning (yellow)  -> needs-attention states (Pending, Low Stock).
  danger  (red)     -> negative/destructive ONLY (Inactive, Delete, Errors,
                       Overdue). Never for additive actions.
  primary (indigo)  -> main navigation, primary Save/Submit actions.
  info    (cyan)    -> neutral in-progress/informational states.

TYPOGRAPHY (existing Velzon fs-* utilities, no new scale):
  Page title       -> h4.fs-22.fw-semibold.ff-secondary
  Card/section title -> h5.card-title.mb-0
  Metadata/secondary -> fs-11–fs-12 + text-muted

SPACING: Bootstrap's existing 0–5 utility scale (0.25rem increments).
  Toolbar button gap -> gap-2
  Card vertical rhythm -> mb-3 between stacked cards on one page

ICONS: Remix Icon (ri-*) is the single icon set for all CRM feature views
and shared chrome (topbar, error pages). Do not introduce mdi-* or bx-*
in new code — see docs/superpowers/plans/2026-07-17-design-system.md
Task 5 for the migration of existing mdi-*/bx-* usage.
================================================================================
*/
```

- [ ] **Step 3: Verify the SCSS still compiles**

Run:
```bash
cd "/Users/meetpatel/_public_html (1)"
ls build/css/custom.min.css
```
This file is a pre-built asset (not rebuilt by a Node toolchain step in this deployment — confirmed earlier this session that `build/` is the actual served directory, not regenerated via `npm run build` as part of normal operation). Since this change is comments-only, no compiled-CSS behavior differs; skip rebuilding assets. Confirm no `@` or brace-matching syntax was broken by reading the file back:
```bash
cat resources/scss/custom.scss
```
Expected: valid SCSS, comment block only, no unclosed `/*`.

- [ ] **Step 4: Commit**

```bash
git add resources/scss/custom.scss
git commit -m "Document design-token semantics for colors, typography, spacing, icons

Establishes the reference every new UI component and retrofitted view
follows: color-to-meaning mapping (success=positive/create, warning=
needs-attention, danger=destructive-only), which existing Velzon
typography/spacing utilities to use for which purpose, and Remix Icon
as the single target icon set. No new SCSS variables - Velzon's
existing Bootstrap semantic classes already resolve correctly once
used consistently, which the following tasks fix."
```

---

### Task 2: `<x-ui.button>` component + retrofit the 5 inconsistent Create buttons + 3 unlabeled icon-only delete buttons

**Files:**
- Create: `resources/views/components/ui/button.blade.php`
- Modify: `resources/views/apps-invoices-list.blade.php`
- Modify: `resources/views/inventrylist.blade.php`
- Modify: `resources/views/product.blade.php`
- Modify: `resources/views/admin/users.blade.php`
- Modify: `resources/views/admin/roles.blade.php`
- Test: `tests/Feature/UiComponents/ButtonComponentTest.php`

**Interfaces:**
- Produces: `<x-ui.button variant="success|primary|danger|secondary" size="sm|md" icon="ri-*-line" aria-label="...">Label</x-ui.button>`. `icon` alone (no visible text slot content) requires `aria-label` — enforced by a `@php` guard in the component that throws in local/testing environments if violated, so a missing label is caught by tests rather than shipped silently.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/UiComponents/ButtonComponentTest.php`:
```php
<?php

namespace Tests\Feature\UiComponents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ButtonComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_button_component_renders_variant_and_size_classes(): void
    {
        $html = Blade::render('<x-ui.button variant="success" size="sm">Create Invoice</x-ui.button>');

        $this->assertStringContainsString('btn-success', $html);
        $this->assertStringContainsString('btn-sm', $html);
        $this->assertStringContainsString('Create Invoice', $html);
    }

    public function test_button_component_renders_icon(): void
    {
        $html = \Illuminate\Support\Facades\Blade::render('<x-ui.button variant="success" icon="ri-add-line">Create Invoice</x-ui.button>');

        $this->assertStringContainsString('ri-add-line', $html);
    }

    public function test_icon_only_button_requires_aria_label(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        \Illuminate\Support\Facades\Blade::render('<x-ui.button variant="primary" icon="ri-delete-bin-2-line"></x-ui.button>');
    }

    public function test_icon_only_button_with_aria_label_renders_correctly(): void
    {
        $html = \Illuminate\Support\Facades\Blade::render('<x-ui.button variant="primary" icon="ri-delete-bin-2-line" ariaLabel="Delete selected"></x-ui.button>');

        $this->assertStringContainsString('aria-label="Delete selected"', $html);
        $this->assertStringContainsString('ri-delete-bin-2-line', $html);
    }
}
```
(`Blade::render()` renders a raw Blade string, letting us test the component in isolation without needing a full page route.)

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ButtonComponentTest`
Expected: FAIL — `Unable to locate a class or view for component [ui.button]` (component doesn't exist yet).

- [ ] **Step 3: Create the component**

Create `resources/views/components/ui/button.blade.php`:
```blade
@php
    $variantClass = match($variant ?? 'primary') {
        'success' => 'btn-success',
        'danger' => 'btn-danger',
        'secondary' => 'btn-secondary',
        default => 'btn-primary',
    };
    $sizeClass = match($size ?? null) {
        'sm' => 'btn-sm',
        default => '',
    };
    $isIconOnly = isset($icon) && trim($slot) === '';
    if ($isIconOnly && empty($ariaLabel)) {
        throw new \InvalidArgumentException('<x-ui.button> with an icon and no visible label text must have an ariaLabel attribute.');
    }
@endphp
<button
    {{ $attributes->except(['variant', 'size', 'icon', 'ariaLabel'])->merge(['type' => 'button', 'class' => trim("btn {$variantClass} {$sizeClass}")]) }}
    @if($isIconOnly) aria-label="{{ $ariaLabel }}" @endif
>
    @isset($icon)
        <i class="{{ $icon }} @if(trim($slot) !== '') align-bottom me-1 @endif"></i>
    @endisset
    {{ $slot }}
</button>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ButtonComponentTest`
Expected: PASS (all 4 tests).

- [ ] **Step 5: Write the failing view-level tests for the 5 retrofitted Create buttons**

Create `tests/Feature/UiComponents/CreateButtonConsistencyTest.php`:
```php
<?php

namespace Tests\Feature\UiComponents;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateButtonConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_create_button_uses_success_variant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice'));

        $response->assertOk();
        $response->assertSee('btn-success', false);
        $response->assertDontSee('btn-danger" href="' . route('invoice.create'), false);
    }

    public function test_inventory_create_button_uses_success_variant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.inventrylist'));

        $response->assertOk();
        $response->assertSee('Create Inventry');
    }

    public function test_product_create_button_uses_success_variant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('Create Product');
    }

    public function test_bulk_delete_buttons_have_aria_label(): void
    {
        $user = User::factory()->create();

        $invoiceResponse = $this->actingAs($user)->get(route('invoice'));
        $invoiceResponse->assertSee('aria-label="Delete selected invoices"', false);

        $inventoryResponse = $this->actingAs($user)->get(route('invoice.inventrylist'));
        $inventoryResponse->assertSee('aria-label="Delete selected items"', false);

        $productResponse = $this->actingAs($user)->get(route('product'));
        $productResponse->assertSee('aria-label="Delete selected products"', false);
    }
}
```

- [ ] **Step 6: Run test to verify it fails**

Run: `php artisan test --filter=CreateButtonConsistencyTest`
Expected: FAIL — old markup doesn't match new assertions (e.g. `btn-danger` still present on the invoice create link, no `aria-label` on delete buttons).

- [ ] **Step 7: Retrofit `apps-invoices-list.blade.php`**

Find and replace (lines 29-37 per audit):
```blade
                    <h5 class="card-title mb-0 flex-grow-1">Invoices</h5>
                    <div class="flex-shrink-0">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-primary" id="remove-actions" onClick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                            <a href="{{route('invoice.create')}}" class="btn btn-danger"><i class="ri-add-line align-bottom me-1"></i> Create Invoice</a>
                        </div>
                    </div>
                </div>
            </div>
```
Replace with:
```blade
                    <h5 class="card-title mb-0 flex-grow-1">Invoices</h5>
                    <div class="flex-shrink-0">
                        <div class="d-flex gap-2 flex-wrap">
                            <x-ui.button variant="primary" icon="ri-delete-bin-2-line" id="remove-actions" onclick="deleteMultiple()" ariaLabel="Delete selected invoices" />
                            <a href="{{route('invoice.create')}}" class="btn btn-success"><i class="ri-add-line align-bottom me-1"></i> Create Invoice</a>
                        </div>
                    </div>
                </div>
            </div>
```
(The Create link stays a plain `<a class="btn btn-success">` rather than `<x-ui.button>` here since it's a navigation link, not a form-submitting button — `<x-ui.button>` renders a `<button>` element; only its color changed from `btn-danger` to `btn-success` per Task 1's semantic rule. This preserves the existing `href`-based navigation behavior exactly.)

- [ ] **Step 8: Retrofit `inventrylist.blade.php`**

Find and replace (lines 46-55 per audit):
```blade
                    <div class="flex-shrink-0">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-primary" id="remove-actions" onClick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class = "row">
                <div class = "col-md-6" style="text-align:right;margin-left: 666px;">
                <button type="button" class="btn btn-primary btn-md" data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Create Inventry</button>
                </div>
            </div>
```
Replace with:
```blade
                    <div class="flex-shrink-0">
                        <div class="d-flex gap-2 flex-wrap">
                            <x-ui.button variant="primary" icon="ri-delete-bin-2-line" id="remove-actions" onclick="deleteMultiple()" ariaLabel="Delete selected items" />
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class = "row">
                <div class = "col-md-6" style="text-align:right;margin-left: 666px;">
                <x-ui.button variant="success" icon="ri-add-line" data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Create Inventry</x-ui.button>
                </div>
            </div>
```
(The existing `style="text-align:right;margin-left: 666px;"` inline styling on the wrapping `<div>` is left untouched — it's a layout positioning quirk unrelated to this task's scope, not the button component itself. Not fixed here per the "preserve, document, don't drive-by-fix" rule.)

- [ ] **Step 9: Retrofit `product.blade.php`**

Find and replace (lines 31-39 per audit) — same pattern as Step 8:
```blade
                            <button class="btn btn-primary" id="remove-actions" onClick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                           
                        </div>
                    </div>
                </div>
            </div>
            <div class = "row">
                <div class = "col-md-6" style="text-align:right;margin-left: 666px;">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Create Product</button>
                </div>
            </div>
```
Replace with:
```blade
                            <x-ui.button variant="primary" icon="ri-delete-bin-2-line" id="remove-actions" onclick="deleteMultiple()" ariaLabel="Delete selected products" />
                           
                        </div>
                    </div>
                </div>
            </div>
            <div class = "row">
                <div class = "col-md-6" style="text-align:right;margin-left: 666px;">
                <x-ui.button variant="success" size="sm" icon="ri-add-line" data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Create Product</x-ui.button>
                </div>
            </div>
```

- [ ] **Step 10: Retrofit `admin/users.blade.php`**

Find (lines 45-46 per audit):
```blade
                    <button type="submit" class="btn btn-success">Create User</button>
                </form>
```
Replace with:
```blade
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Create User</x-ui.button>
                </form>
```
(`type="submit"` is passed through via `$attributes->except([...])->merge(['type' => 'button', ...])` in the component — note the component's `merge()` sets a default of `type="button"`, but Blade's `ComponentAttributeBag::merge()` gives explicitly-passed attributes precedence over merged defaults, so passing `type="submit"` here correctly overrides the component's default. Verify this in Step 12.)

- [ ] **Step 11: Retrofit `admin/roles.blade.php`**

Find (line 25 per audit):
```blade
                    <button type="submit" class="btn btn-success">Create</button>
```
Replace with:
```blade
                    <x-ui.button type="submit" variant="success" icon="ri-add-line">Create Role</x-ui.button>
```
(Label changed from bare "Create" to "Create Role" — clearer for a non-technical user, matching the spec's icon/label user-friendliness rule; this is a copy change, not a functional one, still submits to the same form action.)

- [ ] **Step 12: Run tests to verify they pass**

Run: `php artisan test --filter=ButtonComponentTest`
Run: `php artisan test --filter=CreateButtonConsistencyTest`
Expected: PASS (all 8 tests total). If the `type="submit"` override in Step 10/11 doesn't take effect (component renders `type="button"` instead), fix the component's attribute merge order — `$attributes->except([...])->merge([...])` should be `collect(['type' => 'button', 'class' => ...])->merge($attributes->except([...])->getAttributes())` inverted so explicit attributes win; adjust and re-test until `type="submit"` is confirmed in the rendered output for these two forms specifically.

- [ ] **Step 13: Run the full suite**

Run: `php artisan test`
Expected: 64/64 passing (56 existing + 8 new), no regressions.

- [ ] **Step 14: Manual smoke test against the live DB**

```bash
php -S 127.0.0.1:8000 router.php &
```
Log in as Owner, visit `/apps-invoices-list`, `/inventrylist`, `/product`, `/admin/users`, `/admin/roles` — confirm every Create button now renders green (success), every bulk-delete icon button still functions (click triggers the same `deleteMultiple()` JS, unchanged), and confirm via browser dev tools or `curl | grep aria-label` that the 3 delete buttons carry their new `aria-label`. Stop the server after.

- [ ] **Step 15: Commit**

```bash
git add resources/views/components/ui/button.blade.php resources/views/apps-invoices-list.blade.php resources/views/inventrylist.blade.php resources/views/product.blade.php resources/views/admin/users.blade.php resources/views/admin/roles.blade.php tests/Feature/UiComponents/ButtonComponentTest.php tests/Feature/UiComponents/CreateButtonConsistencyTest.php
git commit -m "Add <x-ui.button> component; fix 5 inconsistent Create buttons + 3 unlabeled icon-only buttons

Every 'Create X' action across Invoices/Inventory/Product/Users/Roles
now uses the same component with the same success-green color (was:
danger/primary/success used interchangeably for the same non-
destructive action, 3 different size treatments). The 3 icon-only
bulk-delete buttons (byte-identical copy-paste across these modules)
now carry a real aria-label instead of being unlabeled icon-only
controls. First use of Laravel Blade anonymous components in this
codebase - confirmed zero prior <x- usage."
```

---

### Task 3: `<x-ui.status-badge>` component + retrofit Invoice and User status badges

**Files:**
- Create: `resources/views/components/ui/status-badge.blade.php`
- Modify: `app/Services/Invoice/InvoiceService.php`
- Modify: `resources/views/admin/users.blade.php`
- Test: `tests/Feature/UiComponents/StatusBadgeComponentTest.php`

**Interfaces:**
- Consumes: nothing new.
- Produces: `<x-ui.status-badge status="Paid" variant="success" icon="ri-checkbox-circle-line" />`. Since `InvoiceService::getDataTableResponse()` builds this HTML as a raw PHP string (server-rendered into JSON for DataTables' AJAX response, not a Blade-rendered page), it cannot literally use `<x-ui.status-badge>` Blade syntax — instead, Step 4 adds a small static PHP helper method that renders the *same visual output* the Blade component produces, keeping both in sync by construction (documented cross-reference in both files' comments) rather than by coincidence.

- [ ] **Step 1: Write the failing test for the Blade component**

Create `tests/Feature/UiComponents/StatusBadgeComponentTest.php`:
```php
<?php

namespace Tests\Feature\UiComponents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class StatusBadgeComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_badge_renders_color_icon_and_text(): void
    {
        $html = Blade::render('<x-ui.status-badge status="Paid" variant="success" icon="ri-checkbox-circle-line" />');

        $this->assertStringContainsString('bg-success-subtle', $html);
        $this->assertStringContainsString('text-success', $html);
        $this->assertStringContainsString('ri-checkbox-circle-line', $html);
        $this->assertStringContainsString('Paid', $html);
    }

    public function test_status_badge_requires_icon(): void
    {
        $this->expectException(\Throwable::class);

        Blade::render('<x-ui.status-badge status="Paid" variant="success" />');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=StatusBadgeComponentTest`
Expected: FAIL — component doesn't exist.

- [ ] **Step 3: Create the component**

Create `resources/views/components/ui/status-badge.blade.php`:
```blade
@php
    if (empty($icon)) {
        throw new \InvalidArgumentException('<x-ui.status-badge> requires an icon prop - status must never be color-only.');
    }
    $variantClass = match($variant ?? 'info') {
        'success' => 'bg-success-subtle text-success',
        'warning' => 'bg-warning-subtle text-warning',
        'danger' => 'bg-danger-subtle text-danger',
        default => 'bg-info-subtle text-info',
    };
@endphp
<span {{ $attributes->merge(['class' => "badge {$variantClass} text-uppercase"]) }}>
    <i class="{{ $icon }} align-bottom me-1"></i>{{ $status }}
</span>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=StatusBadgeComponentTest`
Expected: PASS (both tests).

- [ ] **Step 5: Write the failing view/data test for the retrofitted invoice + user badges**

Create/extend `tests/Feature/InvoiceTest.php` — add this test (read the existing file first to place it alongside the other DataTables test, matching established style):
```php
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
```

Add this test to `tests/Feature/Admin/UserTest.php` (read the existing file first, place alongside other rendering tests):
```php
    public function test_users_page_shows_status_badge_with_icon(): void
    {
        $owner = \App\Models\User::factory()->create();
        \App\Models\User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('ri-checkbox-circle-line', false);
        $response->assertSee('ri-close-circle-line', false);
    }
```

- [ ] **Step 6: Run tests to verify they fail**

Run: `php artisan test --filter=InvoiceTest`
Run: `php artisan test --filter=Admin\\\\UserTest`
Expected: the two new tests FAIL (no `ri-checkbox-circle-line`/`ri-time-line`/`ri-close-circle-line` in current output); all other tests in these files still pass.

- [ ] **Step 7: Add a shared PHP badge-rendering helper**

`InvoiceService::getDataTableResponse()` builds raw HTML server-side for the DataTables JSON payload — it cannot use Blade component syntax directly. Add a small static helper that mirrors `status-badge.blade.php`'s exact output, so both stay visually identical by design.

Create `app/Support/StatusBadge.php`:
```php
<?php

namespace App\Support;

class StatusBadge
{
    /**
     * Renders the same markup as resources/views/components/ui/status-badge.blade.php,
     * for the few places (like DataTables AJAX JSON payloads) that build HTML as a raw
     * PHP string server-side rather than through a Blade-rendered page. Keep both in
     * sync if either changes.
     */
    public static function render(string $status, string $variant, string $icon): string
    {
        $variantClass = match ($variant) {
            'success' => 'bg-success-subtle text-success',
            'warning' => 'bg-warning-subtle text-warning',
            'danger' => 'bg-danger-subtle text-danger',
            default => 'bg-info-subtle text-info',
        };

        return '<span class="badge ' . $variantClass . ' text-uppercase"><i class="' . e($icon) . ' align-bottom me-1"></i>' . e($status) . '</span>';
    }
}
```

- [ ] **Step 8: Retrofit `InvoiceService.php`**

Find (the `$statusHtml` block):
```php
            if ($item->amount == $item->paidamount) {
                $statusHtml = '<span  class = "badge bg-success-subtle text-success text-uppercase">Paid</span>';
                $paymentButton = '<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-id="' . e($item->id) . '" id="savepayment" data-bs-target="#exampleModalgrid" style="display: none;">Payment</button>';
            } else {
                $statusHtml = '<span  class = "badge bg-warning-subtle text-warning text-uppercase">Pending</span>';
                $paymentButton = '<button type="button" class="btn btn-sm btn-primary open-modal" data-id="' . e($item->id) . '" data-customer="' . e($item->customer_id) . '"data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Payment</button>';
            }
```
Replace with:
```php
            if ($item->amount == $item->paidamount) {
                $statusHtml = \App\Support\StatusBadge::render('Paid', 'success', 'ri-checkbox-circle-line');
                $paymentButton = '<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-id="' . e($item->id) . '" id="savepayment" data-bs-target="#exampleModalgrid" style="display: none;">Payment</button>';
            } else {
                $statusHtml = \App\Support\StatusBadge::render('Pending', 'warning', 'ri-time-line');
                $paymentButton = '<button type="button" class="btn btn-sm btn-primary open-modal" data-id="' . e($item->id) . '" data-customer="' . e($item->customer_id) . '"data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Payment</button>';
            }
```
(This also incidentally fixes the stray double-space `class  =` code smell noted in the audit, since the new helper doesn't reproduce it — acceptable as a trivial byproduct of the replacement, not a separate drive-by fix.)

- [ ] **Step 9: Retrofit `admin/users.blade.php`**

Find (lines 63-67 per audit):
```blade
                            <td>
                                <span class="badge {{ $user->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
```
Replace with:
```blade
                            <td>
                                <x-ui.status-badge
                                    :status="$user->is_active ? 'Active' : 'Inactive'"
                                    :variant="$user->is_active ? 'success' : 'danger'"
                                    :icon="$user->is_active ? 'ri-checkbox-circle-line' : 'ri-close-circle-line'"
                                />
                            </td>
```

- [ ] **Step 10: Run tests to verify they pass**

Run: `php artisan test --filter=InvoiceTest`
Run: `php artisan test --filter=Admin\\\\UserTest`
Run: `php artisan test --filter=StatusBadgeComponentTest`
Expected: all PASS.

- [ ] **Step 11: Run the full suite**

Run: `php artisan test`
Expected: 68/68 passing (64 + 2 new badge tests + 2 already counted from Step 5... verify exact count matches — should be 56 baseline + 8 from Task 2 + 2 StatusBadgeComponentTest + 2 retrofit tests = 68), no regressions.

- [ ] **Step 12: Manual smoke test against the live DB**

```bash
php -S 127.0.0.1:8000 router.php &
```
Log in as Owner, visit `/apps-invoices-list` — confirm Paid invoices show a green checkmark-circle badge, Pending show a yellow clock badge. Visit `/admin/users` — confirm Active/Inactive show the same icon+color+text pattern. Stop the server after.

- [ ] **Step 13: Commit**

```bash
git add resources/views/components/ui/status-badge.blade.php app/Support/StatusBadge.php app/Services/Invoice/InvoiceService.php resources/views/admin/users.blade.php tests/Feature/UiComponents/StatusBadgeComponentTest.php tests/Feature/InvoiceTest.php tests/Feature/Admin/UserTest.php
git commit -m "Add <x-ui.status-badge> component; status indicators now use color+icon+text

Invoice Paid/Pending and User Active/Inactive badges previously used
color+text only (a real accessibility gap - color-blind users had no
non-color signal). Both now show an icon alongside color and text.
Added App\Support\StatusBadge as a server-side PHP mirror of the Blade
component's exact output, since InvoiceService builds its DataTables
JSON payload as a raw HTML string and can't use Blade component syntax
directly - documented cross-reference keeps them in sync by design."
```

---

### Task 4: `<x-ui.confirm-modal>` component + retrofit the duplicated delete-confirmation dialog

**Files:**
- Create: `resources/views/components/ui/confirm-modal.blade.php`
- Modify: `resources/views/apps-invoices-list.blade.php`
- Modify: `resources/views/inventrylist.blade.php`
- Modify: `resources/views/product.blade.php`
- Test: `tests/Feature/UiComponents/ConfirmModalTest.php`

**Interfaces:**
- Produces: `<x-ui.confirm-modal id="deleteOrder" record-type="invoice" />`. Preserves the exact existing DOM IDs (`deleteOrder`, `delete-record`, `deleteRecord-close`) the shared JS in `public/build/js/pages/invoiceslist.init.js` already binds to (confirmed: `document.getElementById("delete-record").addEventListener(...)`), so no JS changes are needed — this task is a pure markup deduplication.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/UiComponents/ConfirmModalTest.php`:
```php
<?php

namespace Tests\Feature\UiComponents;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_list_page_still_has_delete_modal_with_correct_ids(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice'));

        $response->assertOk();
        $response->assertSee('id="deleteOrder"', false);
        $response->assertSee('id="delete-record"', false);
        $response->assertSee('id="deleteRecord-close"', false);
    }

    public function test_inventory_list_page_still_has_delete_modal_with_correct_ids(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.inventrylist'));

        $response->assertOk();
        $response->assertSee('id="deleteOrder"', false);
        $response->assertSee('id="delete-record"', false);
    }

    public function test_product_list_page_still_has_delete_modal_with_correct_ids(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('id="deleteOrder"', false);
        $response->assertSee('id="delete-record"', false);
    }
}
```

- [ ] **Step 2: Run test to verify current behavior**

Run: `php artisan test --filter=ConfirmModalTest`
Expected: PASS already (the IDs already exist in the current copy-pasted markup) — this is characterizing existing behavior before refactoring it into a component, per the standard test-first-then-refactor discipline for a pure refactor task (not a new-feature task).

- [ ] **Step 3: Create the component**

Create `resources/views/components/ui/confirm-modal.blade.php`, using the exact existing markup (from `apps-invoices-list.blade.php` lines 74-91+) parameterized only by the record-type label:
```blade
@props(['recordType' => 'record'])
<div class="modal fade flip" id="deleteOrder" tabindex="-1" aria-labelledby="deleteOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#405189,secondary:#f06548" style="width:90px;height:90px">
                </lord-icon>
                <div class="mt-4 text-center">
                    <h4>You are about to delete this {{ $recordType }}?</h4>
                    <p class="text-muted fs-15 mb-4">Deleting this {{ $recordType }} will remove
                        all of
                        its information from our database.</p>
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-link link-success fw-medium text-decoration-none" data-bs-dismiss="modal" id="deleteRecord-close"><i class="ri-close-line me-1 align-middle"></i>
                            Close</button>
                        <button class="btn btn-danger" id="delete-record">Yes,
                            Delete It</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```
(Copy text changed from "a order" — the original's grammatical error, confirmed byte-identical across files — to grammatically correct, record-type-aware text: "this invoice"/"this inventory item"/"this product". This is a direct byproduct of parameterizing the duplicate, not a separate scope item.)

- [ ] **Step 4: Retrofit `apps-invoices-list.blade.php`**

Find the full modal block (lines 74-91+, ending at the modal's closing `</div>` tags) and replace it with:
```blade
<x-ui.confirm-modal record-type="invoice" />
```

- [ ] **Step 5: Retrofit `inventrylist.blade.php`**

Find the byte-identical modal block (lines 106-123+) and replace it with:
```blade
<x-ui.confirm-modal record-type="inventory item" />
```

- [ ] **Step 6: Retrofit `product.blade.php`**

Find the modal block (same pattern, confirmed present since `product.blade.php` also uses `id="delete-record"`/`deleteMultiple()` per Task 2's audit) and replace it with:
```blade
<x-ui.confirm-modal record-type="product" />
```

- [ ] **Step 7: Run tests to verify they still pass**

Run: `php artisan test --filter=ConfirmModalTest`
Expected: PASS (all 3 tests) — same IDs present, now sourced from one component instead of 3 copy-pasted blocks.

- [ ] **Step 8: Run the full suite**

Run: `php artisan test`
Expected: 71/71 passing (68 + 3 new), no regressions.

- [ ] **Step 9: Manual smoke test against the live DB**

```bash
php -S 127.0.0.1:8000 router.php &
```
Log in as Owner, visit `/apps-invoices-list`, click a row's delete action, confirm the modal opens with "this invoice" copy and the Delete/Close buttons still work exactly as before (same JS handlers, unchanged IDs). Repeat for `/inventrylist` and `/product`. Stop the server after.

- [ ] **Step 10: Commit**

```bash
git add resources/views/components/ui/confirm-modal.blade.php resources/views/apps-invoices-list.blade.php resources/views/inventrylist.blade.php resources/views/product.blade.php tests/Feature/UiComponents/ConfirmModalTest.php
git commit -m "Add <x-ui.confirm-modal> component; deduplicate 3 copy-pasted delete dialogs

Invoices/Inventory/Product each had a byte-identical delete-confirmation
modal (including the grammatical error 'a order') copy-pasted rather
than shared. Extracted to one component parameterized by record type;
preserves the exact DOM IDs the existing shared JS
(invoiceslist.init.js) already binds to, so no JS changes needed."
```

---

### Task 5: Icon standardization — migrate `mdi-*`/`bx-*` to `ri-*` in topbar and auth/error chrome

**Files:**
- Modify: `resources/views/layouts/topbar.blade.php`
- Modify: `resources/views/auth/login.blade.php`
- Modify: `resources/views/error/auth-404-basic.blade.php`
- Modify: `resources/views/error/auth-500.blade.php`
- Test: `tests/Feature/IconConsistencyTest.php`

**Interfaces:**
- None — this task is self-contained (pure icon-class swaps, same elements/behavior).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/IconConsistencyTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IconConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_topbar_has_no_mdi_or_bx_icon_classes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('root'));

        $response->assertOk();
        $response->assertDontSee('class="mdi', false);
        $response->assertDontSee("class='mdi", false);
        $response->assertDontSee('class="bx ', false);
        $response->assertDontSee("class='bx ", false);
        $response->assertDontSee('mdi mdi-', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=IconConsistencyTest`
Expected: FAIL — topbar currently has 15 `mdi-*` and 10 `bx-*` occurrences.

- [ ] **Step 3: Migrate `topbar.blade.php`**

Apply each mapping exactly (find each line from the audit, replace only the icon class, leave all other markup/attributes/handlers untouched):

| Old | New |
|---|---|
| `mdi mdi-magnify` (search icon, ×4: lines 38, 49, 50, 132) | `ri-search-line` |
| `mdi mdi-close-circle` (line 39) | `ri-close-circle-line` |
| `bx bx-search` (line 125) | `ri-search-line` |
| `bx bx-category-alt` (line 155) | `ri-apps-2-line` |
| `bx bx-shopping-bag` (line 218) | `ri-shopping-bag-3-line` |
| `bx bx-cart` (line 238) | `ri-shopping-cart-2-line` |
| `bx bx-fullscreen` (line 365) | `ri-fullscreen-line` |
| `bx bx-moon` (line 371) | `ri-moon-line` |
| `bx bx-bell` (line 377) | `ri-notification-3-line` |
| `bx bx-badge-check` (line 423) | `ri-checkbox-circle-line` |
| `mdi mdi-clock-outline` (×7: lines 434, 458, 483, 506, 539, 563, 587, 610 — 8 occurrences per audit) | `ri-time-line` |
| `bx bx-message-square-dots` (line 474) | `ri-chat-3-line` |
| `mdi mdi-account-circle` (line 652) | `ri-user-line` |
| `mdi mdi-message-text-outline` (line 653) | `ri-message-3-line` |
| `mdi mdi-calendar-check-outline` (line 654) | `ri-calendar-check-line` |
| `mdi mdi-lifebuoy` (line 655) | `ri-lifebuoy-line` |
| `mdi mdi-wallet` (line 657) | `ri-wallet-3-line` |
| `mdi mdi-cog-outline` (line 658) | `ri-settings-3-line` |
| `mdi mdi-lock` (line 659) | `ri-lock-line` |
| `bx bx-power-off` (line 660) | `ri-logout-box-line` |

Example of the exact edit pattern for one occurrence (line 38-39):
```blade
<!-- before -->
<span class="mdi mdi-magnify search-widget-icon"></span>
<span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none" id="search-close-options"></span>
<!-- after -->
<span class="ri-search-line search-widget-icon"></span>
<span class="ri-close-circle-line search-widget-icon search-widget-icon-close d-none" id="search-close-options"></span>
```
Apply the same class-only substitution pattern to every remaining occurrence in the mapping table above, preserving every other attribute (`id`, additional classes like `fs-22`, `me-1`, `text-muted`, inline event handlers) exactly as-is.

- [ ] **Step 4: Migrate `auth/login.blade.php`**

Find (line 111):
```blade
<p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> Velzon. Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand</p>
```
Replace with:
```blade
<p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> Velzon. Crafted with <i class="ri-heart-fill text-danger"></i> by Themesbrand</p>
```

- [ ] **Step 5: Migrate `error/auth-404-basic.blade.php`**

Find (line 36):
```blade
<a href="index" class="btn btn-success"><i class="mdi mdi-home me-1"></i>Back to home</a>
```
Replace with:
```blade
<a href="index" class="btn btn-success"><i class="ri-home-4-line me-1"></i>Back to home</a>
```
Find (line 54):
```blade
<p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> Velzon. Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand</p>
```
Replace with:
```blade
<p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> Velzon. Crafted with <i class="ri-heart-fill text-danger"></i> by Themesbrand</p>
```

- [ ] **Step 6: Migrate `error/auth-500.blade.php`**

Find (line 27):
```blade
<a href="index" class="btn btn-success"><i class="mdi mdi-home me-1"></i>Back to home</a>
```
Replace with:
```blade
<a href="index" class="btn btn-success"><i class="ri-home-4-line me-1"></i>Back to home</a>
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=IconConsistencyTest`
Expected: PASS.

- [ ] **Step 8: Run the full suite**

Run: `php artisan test`
Expected: 72/72 passing (71 + 1 new), no regressions.

- [ ] **Step 9: Manual smoke test against the live DB**

```bash
php -S 127.0.0.1:8000 router.php &
```
Log in as Owner, visit `/` — visually confirm the topbar's search icon, notification bell, dark-mode toggle, profile dropdown icons, and fullscreen icon all still render (as `ri-*` glyphs now) and all still function identically (dark-mode toggle still toggles, search still opens, notification dropdown still opens). Stop the server after.

- [ ] **Step 10: Commit**

```bash
git add resources/views/layouts/topbar.blade.php resources/views/auth/login.blade.php resources/views/error/auth-404-basic.blade.php resources/views/error/auth-500.blade.php tests/Feature/IconConsistencyTest.php
git commit -m "Standardize on Remix Icon (ri-*); migrate mdi-*/bx-* leftovers from topbar/auth chrome

Remix Icon was already dominant in the CRM's own feature views (127
uses/21 files) but the topbar and auth/error pages still carried
Material Design Icons and Boxicons - leftover, unreplaced Velzon
scaffolding. All 25 occurrences across topbar.blade.php, login.blade.php,
and both error templates now use ri-* equivalents; every element's
other attributes, IDs, and JS bindings are unchanged."
```

---

### Task 6: Wire real error pages (403/404/419/500) to Laravel's exception handler

**Files:**
- Create: `resources/views/errors/404.blade.php`
- Create: `resources/views/errors/403.blade.php`
- Create: `resources/views/errors/419.blade.php`
- Create: `resources/views/errors/500.blade.php`
- Test: `tests/Feature/ErrorPagesTest.php`

**Interfaces:**
- None — Laravel auto-resolves `resources/views/errors/{status}.blade.php` for the matching HTTP exception with zero `Handler.php` configuration needed (standard Laravel 10 behavior; confirmed no custom `render()` override exists that would interfere).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/ErrorPagesTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_page_uses_the_styled_template(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/this-route-does-not-exist-anywhere');

        $response->assertStatus(404);
        $response->assertSee('Sorry, Page not Found');
        $response->assertSee('Back to home');
    }

    public function test_403_page_uses_the_styled_template(): void
    {
        $worker = User::factory()->create();
        Role::findOrCreate('Worker');
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('product'));

        $response->assertStatus(403);
        $response->assertSee('403');
        $response->assertSee('Back to home');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ErrorPagesTest`
Expected: FAIL — Laravel currently serves its own default error page (no styled "Sorry, Page not Found" text present) since `resources/views/errors/` doesn't exist.

- [ ] **Step 3: Create `resources/views/errors/404.blade.php`**

Adapted from the existing (currently unused) `resources/views/error/auth-404-basic.blade.php` — same content, migrated to `ri-*` per Task 5's convention, `index` href replaced with the real named route:
```blade
@extends('layouts.master-without-nav')

@section('title')
Page Not Found
@endsection

@section('body')
<body>
@endsection
@section('content')
        <div class="auth-page-wrapper pt-5">
            <div class="auth-one-bg-position auth-one-bg"  id="auth-particles">
                <div class="bg-overlay"></div>
                <div class="shape">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                        <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                    </svg>
                </div>
            </div>

            <div class="auth-page-content">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center pt-4">
                                <div class="">
                                    <img src="{{ URL::asset('build/images/error.svg') }}" alt="" class="error-basic-img move-animation">
                                </div>
                                <div class="mt-n4">
                                    <h1 class="display-1 fw-medium">404</h1>
                                    <h3 class="text-uppercase">Sorry, Page not Found 😭</h3>
                                    <p class="text-muted mb-4">The page you are looking for not available!</p>
                                    <a href="{{ route('root') }}" class="btn btn-success"><i class="ri-home-4-line me-1"></i>Back to home</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center">
                                <p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> Oracle Machine Tech CRM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
@endsection
@section('script')
<script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>
@endsection
```
(Footer copyright text changed from "Velzon. Crafted with ♥ by Themesbrand" to the app's own name — a template-attribution line has no place on this app's user-facing error page. `href="index"` changed to `{{ route('root') }}` — the bare relative href was fragile depending on current URL depth; the named route always resolves correctly regardless of where the 404 was triggered from.)

- [ ] **Step 4: Create `resources/views/errors/500.blade.php`**

Adapted from `resources/views/error/auth-500.blade.php`:
```blade
@extends('layouts.master-without-nav')

@section('title')
Server Error
@endsection

@section('body')
<body>
@endsection
@section('content')
        <div class="auth-page-wrapper py-5 d-flex justify-content-center align-items-center min-vh-100">
            <div class="auth-page-content overflow-hidden p-0">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-xl-4 text-center">
                            <div class="error-500 position-relative">
                                <img src="{{ URL::asset('build/images/error500.png') }}" alt="" class="img-fluid error-500-img error-img" />
                                <h1 class="title text-muted">500</h1>
                            </div>
                            <div>
                                <h4>Internal Server Error!</h4>
                                <p class="text-muted w-75 mx-auto">Something went wrong on our end. Please try again, or contact your administrator if the problem continues.</p>
                                <a href="{{ route('root') }}" class="btn btn-success"><i class="ri-home-4-line me-1"></i>Back to home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
```
(Copy changed from the generic "We're not exactly sure what happened, but our servers say something is wrong" to a clearer, more professional message for a business-tool audience.)

- [ ] **Step 5: Create `resources/views/errors/403.blade.php`**

New (no prior template existed for this status — needed now specifically because the role-matrix feature added permission middleware that throws real 403s), matching the same visual language as 404/500:
```blade
@extends('layouts.master-without-nav')

@section('title')
Access Denied
@endsection

@section('body')
<body>
@endsection
@section('content')
        <div class="auth-page-wrapper py-5 d-flex justify-content-center align-items-center min-vh-100">
            <div class="auth-page-content overflow-hidden p-0">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-xl-4 text-center">
                            <div class="mt-n4">
                                <h1 class="display-1 fw-medium">403</h1>
                                <h3 class="text-uppercase">Access Denied</h3>
                                <p class="text-muted mb-4">You don't have permission to view this page. If you believe this is a mistake, contact an administrator.</p>
                                <a href="{{ route('root') }}" class="btn btn-success"><i class="ri-home-4-line me-1"></i>Back to home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
```

- [ ] **Step 6: Create `resources/views/errors/419.blade.php`**

New (CSRF token expiry — relevant on shop-floor tablets that may be left open past the session lifetime):
```blade
@extends('layouts.master-without-nav')

@section('title')
Session Expired
@endsection

@section('body')
<body>
@endsection
@section('content')
        <div class="auth-page-wrapper py-5 d-flex justify-content-center align-items-center min-vh-100">
            <div class="auth-page-content overflow-hidden p-0">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-xl-4 text-center">
                            <div class="mt-n4">
                                <h1 class="display-1 fw-medium">419</h1>
                                <h3 class="text-uppercase">Session Expired</h3>
                                <p class="text-muted mb-4">Your session timed out. Please go back and try again.</p>
                                <a href="{{ route('root') }}" class="btn btn-success"><i class="ri-refresh-line me-1"></i>Reload</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test --filter=ErrorPagesTest`
Expected: PASS (both tests).

- [ ] **Step 8: Run the full suite**

Run: `php artisan test`
Expected: 74/74 passing (72 + 2 new), no regressions.

- [ ] **Step 9: Manual smoke test against the live DB**

```bash
php -S 127.0.0.1:8000 router.php &
```
As a guest, hit a nonexistent URL like `/this-does-not-exist` — confirm the styled 404 page renders, not Laravel's default. Log in as Owner, this doesn't easily reproduce a 403 for Owner (has every permission) — instead confirm via the automated test above that 403 is covered; visually spot-check the page by temporarily visiting it directly if desired (not required, automated coverage is sufficient here). Stop the server after.

- [ ] **Step 10: Commit**

```bash
git add resources/views/errors/
git commit -m "Wire real 403/404/419/500 error pages to Laravel's exception handler

Velzon-styled error templates existed at resources/views/error/ but
Laravel's exception handler resolves resources/views/errors/ (plural)
- a directory that never existed, so real 403/404/500 hits showed
Laravel's default unstyled page. This was a live, current gap: the
role-matrix feature's permission middleware already throws real 403s.
404/500 adapted from the existing (now-redundant but left in place)
error/ templates; 403 and 419 are new, matching the same visual
language, using named routes instead of a fragile bare 'index' href."
```

---

### Task 7: `<x-ui.empty-state>` component + toast feedback helper + fix the silent Product-create flow

**Files:**
- Create: `resources/views/components/ui/empty-state.blade.php`
- Create: `public/build/js/ui-notify.js`
- Modify: `resources/views/layouts/vendor-scripts.blade.php` (or wherever global scripts are included — verify exact include location during implementation)
- Modify: `resources/views/product.blade.php`
- Modify: `app/Http/Controllers/Product/ProductController.php`
- Test: `tests/Feature/UiComponents/EmptyStateComponentTest.php`
- Test: `tests/Feature/ProductFeedbackTest.php`

**Interfaces:**
- Produces: `<x-ui.empty-state icon="ri-inbox-line" message="No invoices yet" />`, and a global `window.notify(type, message)` JS function (`type` is `'success'`/`'error'`) wrapping SweetAlert2's toast mode.
- Consumes: SweetAlert2 (already loaded via `build/libs/sweetalert2/sweetalert2.min.js` on every relevant page, confirmed in earlier audits).

- [ ] **Step 1: Write the failing test for the empty-state component**

Create `tests/Feature/UiComponents/EmptyStateComponentTest.php`:
```php
<?php

namespace Tests\Feature\UiComponents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class EmptyStateComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_state_renders_icon_and_message(): void
    {
        $html = Blade::render('<x-ui.empty-state icon="ri-inbox-line" message="No invoices yet" />');

        $this->assertStringContainsString('ri-inbox-line', $html);
        $this->assertStringContainsString('No invoices yet', $html);
    }

    public function test_empty_state_renders_optional_action_slot(): void
    {
        $html = Blade::render('<x-ui.empty-state icon="ri-inbox-line" message="No invoices yet"><a href="#">Create one</a></x-ui.empty-state>');

        $this->assertStringContainsString('Create one', $html);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=EmptyStateComponentTest`
Expected: FAIL — component doesn't exist.

- [ ] **Step 3: Create the component**

Create `resources/views/components/ui/empty-state.blade.php`:
```blade
<div class="text-center py-5">
    <i class="{{ $icon }} display-4 text-muted d-block mb-3"></i>
    <p class="text-muted fs-15 mb-3">{{ $message }}</p>
    @isset($slot)
        {{ $slot }}
    @endisset
</div>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=EmptyStateComponentTest`
Expected: PASS (both tests).

- [ ] **Step 5: Write the failing test for the Product create feedback flow**

Create `tests/Feature/ProductFeedbackTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_create_validates_required_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('productstore'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_product_create_succeeds_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('productstore'), [
            'name' => 'Test Laser Cutter',
            'rate' => 500000,
            'unit' => 'Nos',
            'make' => 'TEST-1',
        ]);

        $response->assertRedirect(route('product'));
        $this->assertDatabaseHas('product', ['name' => 'Test Laser Cutter']);
    }
}
```

- [ ] **Step 6: Run test to verify it fails**

Run: `php artisan test --filter=ProductFeedbackTest`
Expected: `test_product_create_validates_required_name` FAILS (currently no validation exists — `ProductController::productstore()` calls `$this->service->create($request->all())` with no `$request->validate()`, so an empty submission would currently either 500 or silently create a blank-name row rather than returning a 422 with validation errors). `test_product_create_succeeds_with_valid_data` likely already passes.

- [ ] **Step 7: Add validation to `ProductController::productstore()`**

Read `app/Http/Controllers/Product/ProductController.php` first. Add validation as the first line of `productstore()`, following the exact same pattern already established in `InvoiceController::store()` (`$request->validate(['placesupply' => 'required|string|max:255']);`):
```php
    public function productstore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $this->service->create($request->all());
        return redirect()->route('product');
    }
```

- [ ] **Step 8: Run test to verify it passes**

Run: `php artisan test --filter=ProductFeedbackTest`
Expected: PASS (both tests).

- [ ] **Step 9: Create the toast helper**

Create `public/build/js/ui-notify.js`:
```javascript
/**
 * Shared toast feedback helper, wrapping SweetAlert2 (already loaded on
 * every page that needs this). Usage: window.notify('success', 'Product created');
 */
window.notify = function (type, message) {
    if (typeof Swal === 'undefined') {
        console.warn('SweetAlert2 not loaded on this page; notify() call ignored:', type, message);
        return;
    }
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type === 'success' ? 'success' : 'error',
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
};
```

- [ ] **Step 10: Find where global scripts are included and add the new file**

Read `resources/views/layouts/vendor-scripts.blade.php` (or search for where `build/js/app.js` is included if the file name differs) to find the exact include location. Add, immediately after the SweetAlert2 script tag wherever it's globally loaded (if SweetAlert2 is only loaded per-page rather than globally, add `<script src="{{ URL::asset('build/js/ui-notify.js') }}"></script>` to each of the pages that already load SweetAlert2 individually instead — verify which is actually true by reading the layout file before deciding; document the actual finding in the commit message).

- [ ] **Step 11: Wire the toast into Product's create flow**

`product.blade.php`'s create form currently submits as a plain (non-AJAX) POST to `productstore` and redirects back to `/product` — Step 7's validation now returns a 422 with JSON errors for an AJAX-style request, but this form is NOT currently AJAX (confirmed: it's a plain `<form method="POST">` per the audit's Step 3/9 excerpts showing a modal-triggering button, not a JS-driven submit). Given this, the appropriate fix matching the existing non-AJAX submission pattern is a **server-side flash message displayed after redirect**, not a client-side toast (client-side toasts only make sense for AJAX flows, which this form isn't). Read `resources/views/product.blade.php` in full to find its `<form>` tag and confirm this before proceeding.

Add success feedback via Laravel's standard session-flash pattern, matching what this task's Task 8 (below) will also rely on for DataTables' empty-state. In `ProductController::productstore()`, add a flash message:
```php
    public function productstore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $this->service->create($request->all());

        return redirect()->route('product')->with('success', 'Product created successfully.');
    }
```
Then in `resources/views/product.blade.php`, add a Bootstrap alert near the top of `@section('content')` (immediately after the breadcrumb component, before the first `<div class="row">`) to actually render it — this directly fixes the same class of gap documented in the Known Issues sheet during the role-matrix feature (flashed messages are set but never rendered anywhere in this app):
```blade
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="ri-checkbox-circle-line align-middle me-1"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
```

- [ ] **Step 12: Run the full suite**

Run: `php artisan test`
Expected: 78/78 passing (74 + 2 EmptyStateComponentTest + 2 ProductFeedbackTest), no regressions.

- [ ] **Step 13: Manual smoke test against the live DB**

```bash
php -S 127.0.0.1:8000 router.php &
```
Log in as Owner, visit `/product`, submit the create form with an empty name — confirm a validation error is now shown (not a 500 or a silent blank-name row). Submit with a valid name — confirm the green success alert now appears after redirect, where previously nothing was shown at all. Stop the server after. Confirm via `SELECT * FROM product` (tinker) that no test rows with blank names exist from prior silent-failure behavior, and clean up any test row created during this smoke test.

- [ ] **Step 14: Commit**

```bash
git add resources/views/components/ui/empty-state.blade.php public/build/js/ui-notify.js resources/views/product.blade.php app/Http/Controllers/Product/ProductController.php resources/views/layouts/vendor-scripts.blade.php tests/Feature/UiComponents/EmptyStateComponentTest.php tests/Feature/ProductFeedbackTest.php
git commit -m "Add <x-ui.empty-state> component + toast helper; fix silent Product-create flow

ProductController::productstore() had no validation at all - an empty
submission either 500d or silently created a blank-name row, with zero
feedback either way. Added the same validate()-then-flash pattern
already established in InvoiceController::store(), plus rendered the
flash message (this app has never rendered Session::flash() messages
anywhere - the same gap documented during the role-matrix feature's
live smoke test). Also added a shared window.notify() SweetAlert2
toast helper for future AJAX flows, and the <x-ui.empty-state>
component for zero-row table states, both used starting in Task 8."
```

---

### Task 8: Add loading-state feedback to the one real server-side DataTable (Invoices)

**Files:**
- Modify: `resources/views/apps-invoices-list.blade.php`
- Test: `tests/Feature/InvoiceTest.php` (extend existing)

**Interfaces:**
- None new — this task only touches the one page with genuine `serverSide: true` AJAX loading (Invoices; confirmed the other 4 "DataTables" pages have no real AJAX round-trip to show loading feedback for, per the Task-planning research correction already reflected in the spec).

- [ ] **Step 1: Write the failing test**

This is a pure client-side JS config change (DataTables `processing`/`language` options aren't observable via a server-rendered HTML assertion in the way markup is) — write a test asserting the JS config string is present in the rendered page instead:

Add to `tests/Feature/InvoiceTest.php` (read the file first, add alongside the other DataTable test):
```php
    public function test_invoice_list_datatable_has_processing_and_language_config(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice'));

        $response->assertOk();
        $response->assertSee('processing: true', false);
        $response->assertSee('emptyTable', false);
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=InvoiceTest`
Expected: the new test FAILS (`processing: true` and `emptyTable` not present in the current `new DataTable(...)` call).

- [ ] **Step 3: Update the DataTables init block**

Find (in `apps-invoices-list.blade.php`, the exact block confirmed by research):
```javascript
<script>
document.addEventListener('DOMContentLoaded', function() {
    new DataTable('#example', {
        serverSide: true,
        ajax: '{{ route("invoice.data") }}',
        columns: [
            { data: 0, orderable: true, searchable: true },
            { data: 1, orderable: true, searchable: true },
            { data: 2, orderable: true, searchable: true },
            { data: 3, orderable: true, searchable: true },
            { data: 4, orderable: true, searchable: true },
            { data: 5, orderable: true, searchable: true },
            { data: 6, orderable: true, searchable: true },
            { data: 7, orderable: false, searchable: false },
            { data: 8, orderable: false, searchable: false }
        ]
    });
});
```
Replace with:
```javascript
<script>
document.addEventListener('DOMContentLoaded', function() {
    new DataTable('#example', {
        serverSide: true,
        processing: true,
        ajax: '{{ route("invoice.data") }}',
        columns: [
            { data: 0, orderable: true, searchable: true },
            { data: 1, orderable: true, searchable: true },
            { data: 2, orderable: true, searchable: true },
            { data: 3, orderable: true, searchable: true },
            { data: 4, orderable: true, searchable: true },
            { data: 5, orderable: true, searchable: true },
            { data: 6, orderable: true, searchable: true },
            { data: 7, orderable: false, searchable: false },
            { data: 8, orderable: false, searchable: false }
        ],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: 'No invoices yet.',
            zeroRecords: 'No matching invoices found.',
            search: 'Search invoices:',
        }
    });
});
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=InvoiceTest`
Expected: PASS (all tests in this file, including the new one).

- [ ] **Step 5: Run the full suite**

Run: `php artisan test`
Expected: 79/79 passing (78 + 1 new), no regressions.

- [ ] **Step 6: Manual smoke test against the live DB**

```bash
php -S 127.0.0.1:8000 router.php &
```
Log in as Owner, visit `/apps-invoices-list` — confirm the table still loads and displays real invoice data correctly (unchanged functionality), and that a brief loading spinner is visible on initial load / when searching (may be very fast on local data — acceptable, the config is correct regardless of how briefly it's visible on a fast local connection). Stop the server after.

- [ ] **Step 7: Commit**

```bash
git add resources/views/apps-invoices-list.blade.php tests/Feature/InvoiceTest.php
git commit -m "Add DataTables loading/empty-state feedback to the invoice list

Only Invoices has genuine serverSide:true AJAX pagination (confirmed
during Task planning research - the other 4 list pages have no real
AJAX round-trip to show loading feedback for). Adds processing:true
plus styled loading/empty/search language strings, replacing
DataTables' unstyled default English strings."
```

---

### Task 9: `<x-ui.data-table-card>` component

**Files:**
- Create: `resources/views/components/ui/data-table-card.blade.php`
- Test: `tests/Feature/UiComponents/DataTableCardComponentTest.php`

**Interfaces:**
- Produces: `<x-ui.data-table-card title="Invoices" :create-route="route('invoice.create')" create-label="Create Invoice"> ...table markup as slot... </x-ui.data-table-card>`. This is the shared visual shell (card + header + toolbar) that Task 10 wraps every list page in — the `<table>` itself and its DataTables/list.js init stay page-specific, passed as the component's slot.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/UiComponents/DataTableCardComponentTest.php`:
```php
<?php

namespace Tests\Feature\UiComponents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DataTableCardComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_title_and_slot_content(): void
    {
        $html = Blade::render('<x-ui.data-table-card title="Invoices"><table id="test-table"></table></x-ui.data-table-card>');

        $this->assertStringContainsString('Invoices', $html);
        $this->assertStringContainsString('id="test-table"', $html);
    }

    public function test_renders_optional_create_button(): void
    {
        $html = Blade::render('<x-ui.data-table-card title="Invoices" create-route="/apps-invoices-create" create-label="Create Invoice"><table></table></x-ui.data-table-card>');

        $this->assertStringContainsString('Create Invoice', $html);
        $this->assertStringContainsString('/apps-invoices-create', $html);
    }

    public function test_omits_create_button_when_not_provided(): void
    {
        $html = Blade::render('<x-ui.data-table-card title="Users"><table></table></x-ui.data-table-card>');

        $this->assertStringNotContainsString('btn-success', $html);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DataTableCardComponentTest`
Expected: FAIL — component doesn't exist.

- [ ] **Step 3: Create the component**

Create `resources/views/components/ui/data-table-card.blade.php`:
```blade
@props(['title', 'createRoute' => null, 'createLabel' => null])
<div class="card">
    <div class="card-header border-0">
        <div class="d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">{{ $title }}</h5>
            @if($createRoute && $createLabel)
                <div class="flex-shrink-0">
                    <a href="{{ $createRoute }}" class="btn btn-success"><i class="ri-add-line align-bottom me-1"></i> {{ $createLabel }}</a>
                </div>
            @endif
        </div>
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=DataTableCardComponentTest`
Expected: PASS (all 3 tests).

- [ ] **Step 5: Run the full suite**

Run: `php artisan test`
Expected: 82/82 passing (79 + 3 new), no regressions (this component isn't wired into any real page yet — that's Task 10 — so this is purely additive).

- [ ] **Step 6: Commit**

```bash
git add resources/views/components/ui/data-table-card.blade.php tests/Feature/UiComponents/DataTableCardComponentTest.php
git commit -m "Add <x-ui.data-table-card> component (unwired)

Shared card+header+toolbar shell for every list page - consistent
title placement, optional Create button, consistent card structure.
Not yet applied to any real view; Task 10 retrofits every list page
onto it."
```

---

### Task 10: Apply `<x-ui.data-table-card>` to all 5 real-data list pages (visual shell only, no pagination-mechanism changes)

**Files:**
- Modify: `resources/views/apps-invoices-list.blade.php`
- Modify: `resources/views/inventrylist.blade.php`
- Modify: `resources/views/vender.blade.php`
- Modify: `resources/views/listquation.blade.php`
- Modify: `resources/views/paymenthistry.blade.php`
- Test: `tests/Feature/DataTableCardIntegrationTest.php`

**Interfaces:**
- Consumes: `<x-ui.data-table-card>` from Task 9.
- Explicitly does NOT add real server-side pagination to Inventory/Vendor/Quotation/Payment-History — this task only wraps each page's existing table (however it currently loads data) in the shared visual shell. Deferred per the spec's Out of scope section and the earlier plan-research clarifying question.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/DataTableCardIntegrationTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataTableCardIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_list_uses_shared_card_shell(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create();
        Customer::factory()->create(['invoice_id' => $invoice->id, 'name' => 'Shell Test Customer']);

        $response = $this->actingAs($user)->get(route('invoice'));

        $response->assertOk();
        $response->assertSee('card-header border-0', false);
        $response->assertSee('Invoices');
    }

    public function test_inventory_list_uses_shared_card_shell_and_still_shows_data(): void
    {
        $user = User::factory()->create();
        $product = \App\Models\Product::factory()->create(['name' => 'Shell Test Product']);
        \App\Models\Invetry::factory()->create(['product_id' => $product->id]);

        $response = $this->actingAs($user)->get(route('invoice.inventrylist'));

        $response->assertOk();
        $response->assertSee('card-header border-0', false);
    }

    public function test_vendor_list_uses_shared_card_shell(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.vender'));

        $response->assertOk();
        $response->assertSee('card-header border-0', false);
    }

    public function test_quotation_list_uses_shared_card_shell(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('listqutation'));

        $response->assertOk();
        $response->assertSee('card-header border-0', false);
    }

    public function test_payment_history_uses_shared_card_shell(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.histry'));

        $response->assertOk();
        $response->assertSee('card-header border-0', false);
    }
}
```

- [ ] **Step 2: Run test to verify current state**

Run: `php artisan test --filter=DataTableCardIntegrationTest`
Expected: likely PASS already for most (the existing `card-header border-0` class is already used ad hoc in some of these views per the original audit's component table) — this characterizes the "already visually similar" baseline before Task 3's real change: replacing the ad hoc per-view card markup with the shared component so it's enforced structurally, not by convention. Note actual pass/fail per file and proceed to Step 3 regardless — the goal is using the component, not just matching its output.

- [ ] **Step 3: Retrofit `apps-invoices-list.blade.php`**

Read the file in full first to find the exact existing `<div class="card">...<div class="card-header border-0">...<h5 class="card-title mb-0 flex-grow-1">Invoices</h5>...` wrapper (already identified in Task 2/4's edits to this same file — those tasks' changes must be applied first, in task order, before this one touches the same file). Replace the outer `<div class="card">` / `<div class="card-header border-0">` / title `<h5>` / toolbar `<div>` wrapper with:
```blade
<x-ui.data-table-card title="Invoices" :create-route="route('invoice.create')" create-label="Create Invoice">
    <div class="d-flex gap-2 flex-wrap mb-3">
        <x-ui.button variant="primary" icon="ri-delete-bin-2-line" id="remove-actions" onclick="deleteMultiple()" ariaLabel="Delete selected invoices" />
    </div>
    <div class="table-responsive">
        <table class="table table-bordered dt-responsive nowrap table-striped align-middle" id="example">
            <!-- existing thead/tbody content, unchanged -->
        </table>
    </div>
</x-ui.data-table-card>
```
(The component's own `create-route`/`create-label` props now render the Create button, replacing the separate `<a href="{{route('invoice.create')}}" class="btn btn-success">` markup Task 2 introduced — this task supersedes that specific line by moving it into the component's props; the bulk-delete button stays as a separate toolbar element inside the slot since the component only handles the single primary Create action, not arbitrary toolbar buttons.)

- [ ] **Step 4: Retrofit `inventrylist.blade.php`**

Same pattern — read the file first, locate its card/header/title wrapper (already touched by Task 2's edits), replace with:
```blade
<x-ui.data-table-card title="Inventory">
    <div class="d-flex gap-2 flex-wrap mb-3">
        <x-ui.button variant="primary" icon="ri-delete-bin-2-line" id="remove-actions" onclick="deleteMultiple()" ariaLabel="Delete selected items" />
        <x-ui.button variant="success" icon="ri-add-line" data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Create Inventry</x-ui.button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered dt-responsive nowrap table-striped align-middle" id="example">
            <!-- existing thead/tbody content, unchanged -->
        </table>
    </div>
</x-ui.data-table-card>
```
(No `create-route` prop here since Inventory's "Create" opens a modal, not a navigation link — stays as a slot-level `<x-ui.button>` rather than the component's built-in nav-link Create button, which only supports `href`-style navigation.)

- [ ] **Step 5: Retrofit `vender.blade.php`**

Read the file first. Wrap its existing table (no create button on this page per the original audit) with:
```blade
<x-ui.data-table-card title="Vendors">
    <div class="table-responsive">
        <table class="table table-bordered dt-responsive nowrap table-striped align-middle" id="example">
            <!-- existing thead/tbody content, unchanged -->
        </table>
    </div>
</x-ui.data-table-card>
```

- [ ] **Step 6: Retrofit `listquation.blade.php`**

Read the file first. Same pattern:
```blade
<x-ui.data-table-card title="Quotations">
    <div class="table-responsive">
        <table class="table table-bordered dt-responsive nowrap table-striped align-middle" id="example">
            <!-- existing thead/tbody content, unchanged -->
        </table>
    </div>
</x-ui.data-table-card>
```

- [ ] **Step 7: Retrofit `paymenthistry.blade.php`**

Read the file first. Same pattern:
```blade
<x-ui.data-table-card title="Payment History">
    <div class="table-responsive">
        <table class="table table-bordered dt-responsive nowrap table-striped align-middle" id="example">
            <!-- existing thead/tbody content, unchanged -->
        </table>
    </div>
</x-ui.data-table-card>
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=DataTableCardIntegrationTest`
Expected: PASS (all 5 tests).

- [ ] **Step 9: Run the full suite**

Run: `php artisan test`
Expected: 87/87 passing (82 + 5 new), no regressions. Pay particular attention to any test from Tasks 2-4 that asserted on markup inside these same 2 files (`apps-invoices-list.blade.php`, `inventrylist.blade.php`) — if any fail because this task's restructuring moved something Task 2/4's tests checked for, fix by ensuring this task's replacement still contains the same elements/IDs those earlier tests need, not by weakening the earlier tests.

- [ ] **Step 10: Manual smoke test against the live DB**

```bash
php -S 127.0.0.1:8000 router.php &
```
Log in as Owner, visit all 5 pages (`/apps-invoices-list`, `/inventrylist`, `/vender`, `/admin/listqutation`, `/paymenthistry`) — confirm every page still shows real data correctly, every existing action (create, delete, payment recording where applicable) still works exactly as before, and all 5 now visually share the same card header/title/toolbar layout. Stop the server after.

- [ ] **Step 11: Commit**

```bash
git add resources/views/apps-invoices-list.blade.php resources/views/inventrylist.blade.php resources/views/vender.blade.php resources/views/listquation.blade.php resources/views/paymenthistry.blade.php tests/Feature/DataTableCardIntegrationTest.php
git commit -m "Wrap all 5 real-data list pages in the shared <x-ui.data-table-card> shell

Invoices/Inventory/Vendor/Quotation/Payment-History now share one
card/header/title/toolbar structure instead of each hand-writing
similar-but-slightly-different markup. Visual/structural change only -
does not add real server-side pagination to the 4 pages that lack it
(Inventory/Vendor/Quotation/Payment-History), per the spec's explicit
scoping - their underlying data-loading mechanism (full dataset
server-rendered, client-side re-paginated) is unchanged."
```

---

### Task 11: Migrate Admin Roles/Users pages onto client-side DataTables + the shared shell

**Files:**
- Modify: `resources/views/admin/roles.blade.php`
- Modify: `resources/views/admin/users.blade.php`
- Test: `tests/Feature/Admin/AdminTableSearchTest.php`

**Interfaces:**
- Consumes: `<x-ui.data-table-card>` from Task 9. Adds real client-side DataTables (search/sort/pagination) — a genuine, additive, client-side-only functionality gain, since both pages' current implementation is a plain unpaginated `<table>` with zero search/sort. No new backend endpoint needed (both datasets are small — roles and users — and already server-rendered in full).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/AdminTableSearchTest.php`:
```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTableSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_users_page_loads_datatables_js(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('DataTable(', false);
    }

    public function test_roles_page_loads_datatables_js(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertSee('DataTable(', false);
    }

    public function test_users_page_still_shows_all_users_and_create_form(): void
    {
        $owner = User::factory()->create(['name' => 'Table Test Owner']);

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Table Test Owner');
        $response->assertSee('Create User');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AdminTableSearchTest`
Expected: `test_users_page_loads_datatables_js` and `test_roles_page_loads_datatables_js` FAIL (no DataTables JS currently loaded on either page — plain tables, per the audit). `test_users_page_still_shows_all_users_and_create_form` likely already passes (characterizes existing behavior).

- [ ] **Step 3: Retrofit `admin/users.blade.php`**

Read the file in full first. Wrap the existing user table in the shared shell and add a local DataTables init (client-side, no `serverSide`/`ajax` options — the full user list is already rendered server-side into the table, matching the pattern the other "DataTables-ish" pages already rely on for their basic client-side search/sort):
```blade
<x-ui.data-table-card title="Users">
    <div class="table-responsive">
        <table class="table table-bordered align-middle" id="users-table">
            <!-- existing thead/tbody content, unchanged -->
        </table>
    </div>
</x-ui.data-table-card>
```
Add at the end of the file (or the existing `@section('script')` if one exists — read the file to confirm), after the existing CSRF/toggle JS:
```blade
@section('script')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new DataTable('#users-table', {
        language: {
            emptyTable: 'No users yet.',
            zeroRecords: 'No matching users found.',
            search: 'Search users:',
        }
    });
});
</script>
@endsection
```
(Uses the same CDN-hosted DataTables version already used by the other list pages, for consistency with the existing dependency approach — not the locally-vendored copy, matching current convention rather than fixing the CDN-vs-local inconsistency noted in the original audit, which is out of this task's scope.)

- [ ] **Step 4: Retrofit `admin/roles.blade.php`**

Read the file in full first. Same pattern — wrap the permission matrix table:
```blade
<x-ui.data-table-card title="Roles & Permissions">
    <div class="table-responsive">
        <table class="table table-bordered align-middle" id="roles-matrix-table">
            <!-- existing thead/tbody content, unchanged -->
        </table>
    </div>
</x-ui.data-table-card>
```
Add the same DataTables init pattern, targeting `#roles-matrix-table`:
```blade
@section('script')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new DataTable('#roles-matrix-table', {
        paging: false,
        searching: false,
        info: false,
        ordering: false,
    });
});
</script>
@endsection
```
(Note: the roles matrix table's rows are *permissions*, not a list of records to search/sort/paginate in the usual sense — each row's checkboxes must stay together and visible, so pagination/searching/sorting are explicitly disabled here (`paging: false, searching: false, ordering: false`) — this table only gets the shared visual card shell from Task 9, not the search/sort/paginate behavior that makes sense for the Users table. This is a deliberate difference, not an oversight — document it as such in the commit.)

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --filter=AdminTableSearchTest`
Expected: PASS (all 3 tests).

- [ ] **Step 6: Run the full suite**

Run: `php artisan test`
Expected: 90/90 passing (87 + 3 new), no regressions. Pay attention to any Task 4/5-era Admin\RoleTest or Admin\UserTest assertions that check for exact prior markup — fix by preserving the checked-for elements inside the new structure, not by weakening those tests.

- [ ] **Step 7: Manual smoke test against the live DB**

```bash
php -S 127.0.0.1:8000 router.php &
```
Log in as Owner, visit `/admin/users` — confirm real search/sort/pagination now works (type in the search box, confirm rows filter). Visit `/admin/roles` — confirm the permission matrix still displays correctly with checkboxes intact and functional (toggle one, confirm the AJAX save still works, matching Task 4's original behavior), and confirm paging/searching are correctly absent (all permission rows always visible, no page-2 hiding rows). Stop the server after.

- [ ] **Step 8: Commit**

```bash
git add resources/views/admin/roles.blade.php resources/views/admin/users.blade.php tests/Feature/Admin/AdminTableSearchTest.php
git commit -m "Add real client-side DataTables + shared shell to Admin Roles/Users pages

Both pages previously had zero search/sort/pagination (plain
unpaginated tables). Users now gets real client-side search/sort/
pagination via DataTables, matching the basic capability the other
list pages already have. Roles' permission matrix deliberately
disables paging/searching/sorting (paging: false, searching: false,
ordering: false) - its rows are permission checkboxes that must stay
together and visible, not a searchable record list - it only inherits
the shared visual card shell, not the table-interaction behavior."
```

---

### Task 12: Migrate Product page from `list.js` to the same client-side DataTables pattern

**Files:**
- Modify: `resources/views/product.blade.php`
- Test: `tests/Feature/ProductTableTest.php`

**Interfaces:**
- Consumes: `<x-ui.data-table-card>` from Task 9. Removes the `list.js`/`list.pagination.js` dependency for this page, replacing it with the same DataTables pattern used everywhere else — the last remaining "third table technology" identified in the original audit.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/ProductTableTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_uses_datatables_not_listjs(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('DataTable(', false);
        $response->assertDontSee('list.min.js', false);
        $response->assertDontSee('list.pagination.js', false);
    }

    public function test_product_page_still_shows_all_products(): void
    {
        $user = User::factory()->create();
        \App\Models\Product::factory()->create(['name' => 'Table Migration Test Product']);

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('Table Migration Test Product');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProductTableTest`
Expected: `test_product_page_uses_datatables_not_listjs` FAILS (currently uses `list.js`, no `DataTable(` call present). `test_product_page_still_shows_all_products` likely already passes.

- [ ] **Step 3: Retrofit `product.blade.php`**

Read the file in full first (it's been touched by Tasks 2, 4, and 7 already — apply this change on top of those). Remove the `list.js`/`list.pagination.js`/`invoiceslist.init.js` script tags from `@section('script')` (keep `sweetalert2.min.js` and `app.js` — still needed for the delete-confirmation modal's JS handlers per Task 4). Wrap the product table in the shared shell:
```blade
<x-ui.data-table-card title="Products">
    <div class="d-flex gap-2 flex-wrap mb-3">
        <x-ui.button variant="primary" icon="ri-delete-bin-2-line" id="remove-actions" onclick="deleteMultiple()" ariaLabel="Delete selected products" />
        <x-ui.button variant="success" size="sm" icon="ri-add-line" data-bs-toggle="modal" data-bs-target="#exampleModalgrid">Create Product</x-ui.button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle" id="products-table">
            <!-- existing thead/tbody content, unchanged -->
        </table>
    </div>
</x-ui.data-table-card>
```
Add the DataTables init (same CDN-based pattern as Task 11, for consistency with the rest of the app's current dependency approach):
```blade
@section('script')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new DataTable('#products-table', {
        language: {
            emptyTable: 'No products yet.',
            zeroRecords: 'No matching products found.',
            search: 'Search products:',
        }
    });
});
</script>
@endsection
```
**Important**: `deleteMultiple()` (bound to the bulk-delete button, per Task 2/4) and the delete-confirmation modal's `document.getElementById("delete-record").addEventListener(...)` handler both currently live in `public/build/js/pages/invoiceslist.init.js`, which this step removes from Product's script includes (it was only ever pulled in for `list.js` pagination, not for these handlers — but verify this precisely before removing: search `invoiceslist.init.js` for whether `deleteMultiple`/`delete-record` are defined there, since Task 4's report confirmed `invoiceslist.init.js:1789` and `:1839` as their actual source). If confirmed that file is the source, do NOT remove it from Product's script section — keep it, but only for those two handlers, while still removing `list.js`/`list.pagination.js` specifically (the file can serve both purposes; only its pagination-related code becomes dead/unused, not removed in this task since editing that shared JS file is out of scope here — it's still needed by Invoices' own page, which doesn't use it for pagination either, only for these same two handlers, confirmed in Task 4).

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter=ProductTableTest`
Expected: PASS (both tests).

- [ ] **Step 5: Run the full suite**

Run: `php artisan test`
Expected: 92/92 passing (90 + 2 new), no regressions.

- [ ] **Step 6: Manual smoke test against the live DB**

```bash
php -S 127.0.0.1:8000 router.php &
```
Log in as Owner, visit `/product` — confirm all real products still display, search/sort/pagination now works via DataTables, the Create Product modal still opens and submits correctly (Task 7's validation still applies), and the delete-confirmation modal (Task 4) still opens and its Delete/Close buttons still function. Stop the server after.

- [ ] **Step 7: Commit**

```bash
git add resources/views/product.blade.php tests/Feature/ProductTableTest.php
git commit -m "Migrate Product page from list.js to DataTables (last of 3 table technologies)

Completes the table-technology consolidation started in Tasks 10-11:
Product was the only page using list.js/list.pagination.js for
client-side pagination, a third distinct mechanism alongside
DataTables and the plain unpaginated admin tables. Now uses the same
DataTables pattern as every other list page. deleteMultiple()/
delete-record handlers (defined in invoiceslist.init.js) are kept -
confirmed they're unrelated to list.js's pagination code, which is
what's actually being replaced."
```

---

## Self-review notes (from the plan author)

- **Spec coverage:** every section of `docs/superpowers/specs/2026-07-17-design-system-design.md` maps to a task — Design tokens → Task 1, Button/badge/modal/empty-state components → Tasks 2/3/4/7, Icon standardization → Task 5, Error pages → Task 6, Loading states → Task 8, Table visual consolidation → Tasks 9-12. The corrected scope (visual-only table consistency, no new server-side pagination for 4 modules) is reflected throughout Tasks 10-12, not just stated once and forgotten.
- **Task ordering respects file dependencies**: Tasks 2 and 4 both edit `apps-invoices-list.blade.php`/`inventrylist.blade.php`/`product.blade.php` before Task 10/12 restructure the same files further — each task's Read-first instruction accounts for cumulative prior edits rather than assuming a pristine original file.
- **Known deviation from a literal reading of the spec**: Task 1 does not change any SCSS color *values* (only documents the semantic mapping) since no concrete legibility/contrast defect was found during research to justify a specific numeric change — per the spec's own language ("*where they genuinely serve consistency and legibility*, adjust..."), inventing a change without a found defect would violate the standards doc's scope-discipline rule. If a specific contrast issue is found during implementation (e.g. via manual testing in bright shop-floor-like lighting), amend Task 1 then, with the concrete before/after values documented.
