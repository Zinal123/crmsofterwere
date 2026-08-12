# Quotation Auto-Print · Problem-Type Enrichment · Worker Summary · Redesign Sweep — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship four independent, user-approved improvements to the Oracle Machine Tech CRM: real auto-printing quotation PDFs, a richer ticket problem-type system (more categories + metadata + a link into the job-traveler), a shop-floor "today at a glance" strip on the worker landing, and an app-wide visual-consistency sweep of the indigo/navy rebrand.

**Architecture:** Laravel 10 / PHP 8.2, MySQL prod / SQLite tests (FK enforcement ON). Repository→service pattern, Blade + Bootstrap 5 (Velzon template), Spatie permissions, PHPUnit. Each of the four Plans (A–D) is independently executable and independently testable; they share only the Global Constraints below.

**Tech Stack:** barryvdh/laravel-dompdf ^3.1 (already installed), Spatie roles/permissions, `App\Support\Auditing\Auditable` trait, existing `<x-ui.*>` Blade components, brand cascade in `public/build/css/brand-theme.css` + `resources/views/layouts/partials/theme-overrides.blade.php`.

## Global Constraints

- **FK columns** must be `foreignId`/`unsignedBigInteger`, NEVER `integer` — an `integer` FK has broken a clean MySQL migrate before. Every new FK uses `foreignId(...)->constrained(...)` with an explicit `nullOnDelete()`/`restrictOnDelete()`/`cascadeOnDelete()`.
- **Auditable models** (`use Auditable`) must be deleted via a model `->delete()` (fires events), never a query-builder delete. New audit-worthy fields go in the model's `$auditStatusFields` where the model already declares one.
- **Tests run on SQLite with FK enforcement ON.** Enum-like columns use `string` + an application-level `in:` validation rule, never a DB `enum()` that a later value change can't migrate cleanly (follow the existing pattern — but note `ticket_problem_types.category` is currently a DB `enum`; Plan B Task 1 converts it to `string`).
- **UserFactory attaches the Owner role by default.** To isolate a role in a test, use `->syncRoles(['Worker'])`, not `assignRole` (Owner would leak all permissions). See the codebase memory on this gotcha.
- **New permissions** must be added to `RolesAndPermissionsSeeder::PERMISSIONS` and granted to the roles that need them; deploying any plan that adds one requires re-running that seeder.
- **Migrations** must be reversible (`down()` drops what `up()` added) and must clean-migrate from zero on both MySQL and SQLite.
- Match surrounding code style; reuse existing `<x-ui.*>` components and service/repository patterns rather than introducing new ones.

---

# PLAN A — Quotation PDF Auto-Print

**Context:** The quotation "Download" button (`resources/views/listquation.blade.php:60`) already links to `route('quation.pdf', $id)` → `QutationController::print()` (`app/Http/Controllers/Quotation/QutationController.php:32`), which already returns a real DomPDF download of `resources/views/pdf/quotation.blade.php`. So the "real PDF" half is DONE. The only gap is **auto-print**: the user wants a one-click path that opens the print dialog. We add a second action ("Print") that streams the same PDF inline with a DomPDF OpenAction that triggers `this.print()` in viewers that support it.

## File Structure
- Modify: `app/Http/Controllers/Quotation/QutationController.php` — add `printInline($id)`.
- Modify: `routes/web.php` — add `quation.print` route (same permission as `quation.pdf`).
- Modify: `resources/views/listquation.blade.php` — add a "Print" button beside "Download".
- Modify: `resources/views/pdf/quotation.blade.php` — add the auto-print script (guarded so it only fires in the inline/print variant).
- Test: `tests/Feature/Quotation/QuotationPrintTest.php`.

### Task A1: Inline auto-printing PDF stream

**Files:**
- Modify: `app/Http/Controllers/Quotation/QutationController.php:32-38`
- Modify: `routes/web.php:163` (add a route directly after `quation.pdf`)
- Modify: `resources/views/pdf/quotation.blade.php`
- Modify: `resources/views/listquation.blade.php:60`
- Test: `tests/Feature/Quotation/QuotationPrintTest.php`

**Interfaces:**
- Consumes: `$this->service->getQuotationPdfData($id)` (existing, returns `['quotation' => ..., ...]`), `Barryvdh\DomPDF\Facade\Pdf`.
- Produces: route `quation.print` (name), `QutationController::printInline($id)` returning an inline `Response` with `Content-Type: application/pdf`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Quotation/QuotationPrintTest.php
namespace Tests\Feature\Quotation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationPrintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_inline_print_streams_a_pdf_disposition_inline(): void
    {
        $owner = User::factory()->create(); // Owner role by default → has quotations.download-pdf
        $id = $this->makeQuotation();

        $res = $this->actingAs($owner)->get(route('quation.print', $id));

        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('content-type'));
        $this->assertStringContainsString('inline', (string) $res->headers->get('content-disposition'));
    }

    public function test_print_requires_the_download_permission(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']); // no quotations.download-pdf
        $id = $this->makeQuotation();

        $this->actingAs($worker)->get(route('quation.print', $id))->assertForbidden();
    }

    // Reuse whatever the existing quotation tests use to build a quotation row.
    // Inspect tests/Feature/Quotation/* for the exact factory/seed helper and
    // copy it here (do NOT invent a new schema).
    private function makeQuotation(): int
    {
        // TODO in Step 3 context: fill from the existing quotation test helpers.
    }
}
```

- [ ] **Step 2: Wire the helper from existing tests**

Read `tests/Feature/Quotation/` (and `app/Services/Quotation/`) to find how a quotation row is created in existing tests, and implement `makeQuotation()` to return a valid id. Run:
`php artisan test tests/Feature/Quotation/QuotationPrintTest.php` → Expected: FAIL (route `quation.print` not defined).

- [ ] **Step 3: Add the controller method**

In `app/Http/Controllers/Quotation/QutationController.php`, add after `print()`:

```php
    public function printInline($id)
    {
        $data = $this->service->getQuotationPdfData($id);
        $data['autoPrint'] = true;
        $pdf = Pdf::loadView('pdf.quotation', $data)->setPaper('a4');
        $pdf->getDomPDF()->getOptions()->set('isJavascriptEnabled', true);

        return $pdf->stream('Quotation-' . $data['quotation']->id . '.pdf');
    }
```

(`stream()` sends `Content-Disposition: inline`; `download()` in the existing `print()` stays as the plain-save path.)

- [ ] **Step 4: Add the route**

In `routes/web.php` directly after the `quation.pdf` line (163):

```php
    Route::get('/printquation/{id}/print', [App\Http\Controllers\Quotation\QutationController::class, 'printInline'])->name('quation.print')->middleware('permission:quotations.download-pdf');
```

- [ ] **Step 5: Add the auto-print script to the PDF view**

At the very end of `resources/views/pdf/quotation.blade.php`, add:

```blade
@if(!empty($autoPrint))
<script type="text/javascript">this.print();</script>
@endif
```

DomPDF converts `this.print()` into a PDF OpenAction that opens the print dialog in viewers that honor it (Adobe Reader; desktop browser viewers vary). The plain download path (`$autoPrint` unset) never emits it.

- [ ] **Step 6: Add the "Print" button to the list**

In `resources/views/listquation.blade.php` immediately after the Download anchor (line 60):

```blade
                                   <a href="{{route('quation.print' ,$item->id)}}" target="_blank" rel="noopener" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" title="Print Quotation" aria-label="Print Quotation"><i class="ri-printer-line align-bottom"></i></a>
```

- [ ] **Step 7: Run the tests**

Run: `php artisan test tests/Feature/Quotation/QuotationPrintTest.php` → Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Quotation/QutationController.php routes/web.php resources/views/pdf/quotation.blade.php resources/views/listquation.blade.php tests/Feature/Quotation/QuotationPrintTest.php
git commit -m "feat: add inline auto-print action for quotation PDFs"
```

---

# PLAN B — Problem-Type Enrichment (categories + metadata + traveler link + ticket priority)

**Context:** `ticket_problem_types` today has only `category` (DB `enum('electrical','mechanical')`), `name`, `is_active` (`database/migrations/2026_07_26_111402_create_ticket_problem_types_table.php`). The controller validates `category` as `in:electrical,mechanical` and the form (`resources/views/ticketing/problem-types/index.blade.php`) exposes only `category` + `name`. Tickets (`tickets` table) have **no priority column**. Jobs created from a ticket go through `TicketService::assign()` (`app/Services/Ticketing/TicketService.php:69`), which calls `jobService->createAssigned([...])`. This plan: widen the category set, add `description`, `default_priority`, `estimated_resolution_hours`, and a nullable `checklist_template_id` FK; add a `priority` column to tickets defaulted from the problem type at raise-time; and auto-apply the linked checklist template to the job when a ticket is assigned.

## File Structure
- Migrate: `ticket_problem_types` — add columns (Task 1).
- Migrate: `tickets` — add `priority` (Task 4).
- Modify model `app/Models/TicketProblemType.php`, `app/Models/Ticket.php`.
- Modify `app/Http/Controllers/Ticketing/TicketProblemTypeController.php` + its service.
- Modify `resources/views/ticketing/problem-types/index.blade.php`.
- Modify ticket-create flow (`app/Services/Ticketing/TicketService.php`, client + admin ticket create views/controllers).
- Wire checklist auto-apply into `TicketService::assign()` via `ChecklistTemplateService`.

### Task 1: Widen and enrich the `ticket_problem_types` schema

**Files:**
- Create: `database/migrations/2026_08_13_000001_enrich_ticket_problem_types.php`
- Modify: `app/Models/TicketProblemType.php`
- Test: `tests/Feature/Ticketing/ProblemTypeEnrichmentTest.php`

**Interfaces:**
- Produces: `ticket_problem_types` columns `description` (text, nullable), `default_priority` (string, default `'medium'`), `estimated_resolution_hours` (unsignedInteger, nullable), `checklist_template_id` (nullable FK → `checklist_templates`, `nullOnDelete`); `category` converted from `enum` to `string`. Model `$fillable` gains all four; `$casts` gains `estimated_resolution_hours => 'integer'`.
- Allowed category values (app-level validation, not DB): `electrical`, `mechanical`, `hydraulic`, `pneumatic`, `plc_software`, `calibration`, `consumable`, `other`.
- Allowed priority values: `low`, `medium`, `high`, `urgent`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Ticketing/ProblemTypeEnrichmentTest.php
namespace Tests\Feature\Ticketing;

use App\Models\ChecklistTemplate;
use App\Models\TicketProblemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProblemTypeEnrichmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_problem_type_persists_all_new_metadata(): void
    {
        $tpl = ChecklistTemplate::create(['name' => 'Hydraulic PM', 'is_active' => true]);

        $type = TicketProblemType::create([
            'category' => 'hydraulic',
            'name' => 'Pump seal leak',
            'description' => 'Visible fluid under the pump housing.',
            'default_priority' => 'high',
            'estimated_resolution_hours' => 4,
            'checklist_template_id' => $tpl->id,
            'is_active' => true,
        ]);

        $fresh = $type->fresh();
        $this->assertSame('hydraulic', $fresh->category);
        $this->assertSame('high', $fresh->default_priority);
        $this->assertSame(4, $fresh->estimated_resolution_hours);
        $this->assertSame($tpl->id, $fresh->checklist_template_id);
    }

    public function test_deleting_the_linked_template_nulls_the_fk_not_the_type(): void
    {
        $tpl = ChecklistTemplate::create(['name' => 'X', 'is_active' => true]);
        $type = TicketProblemType::create([
            'category' => 'other', 'name' => 'Misc', 'checklist_template_id' => $tpl->id, 'is_active' => true,
        ]);

        $tpl->delete();

        $this->assertDatabaseHas('ticket_problem_types', ['id' => $type->id, 'checklist_template_id' => null]);
    }
}
```

- [ ] **Step 2: Run it — expect failure**

Run: `php artisan test tests/Feature/Ticketing/ProblemTypeEnrichmentTest.php` → Expected: FAIL (unknown columns).

- [ ] **Step 3: Write the migration**

```php
<?php
// database/migrations/2026_08_13_000001_enrich_ticket_problem_types.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // category: enum -> string (so new categories need no enum migration).
        // SQLite can't ALTER an enum; recreate via a temporary string column.
        Schema::table('ticket_problem_types', function (Blueprint $table) {
            $table->string('category_tmp')->nullable()->after('id');
        });
        \DB::table('ticket_problem_types')->update(['category_tmp' => \DB::raw('category')]);
        Schema::table('ticket_problem_types', function (Blueprint $table) {
            $table->dropColumn('category');
        });
        Schema::table('ticket_problem_types', function (Blueprint $table) {
            $table->renameColumn('category_tmp', 'category');
        });

        Schema::table('ticket_problem_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('default_priority')->default('medium')->after('description');
            $table->unsignedInteger('estimated_resolution_hours')->nullable()->after('default_priority');
            $table->foreignId('checklist_template_id')->nullable()->after('estimated_resolution_hours')
                ->constrained('checklist_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_problem_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checklist_template_id');
            $table->dropColumn(['description', 'default_priority', 'estimated_resolution_hours']);
        });
        // category stays a string on rollback (acceptable — no data loss).
    }
};
```

> Note for the implementer: if `renameColumn`/`dropColumn` on SQLite gives trouble under the installed `doctrine/dbal` state, fall back to recreating the column set within a single `Schema::table` using raw statements guarded by `DB::getDriverName()`. Verify `php artisan migrate:fresh` runs clean on SQLite before committing.

- [ ] **Step 4: Update the model**

In `app/Models/TicketProblemType.php`, set:

```php
    protected $fillable = [
        'category',
        'name',
        'description',
        'default_priority',
        'estimated_resolution_hours',
        'checklist_template_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'estimated_resolution_hours' => 'integer',
    ];

    public function checklistTemplate()
    {
        return $this->belongsTo(\App\Models\ChecklistTemplate::class);
    }
```

- [ ] **Step 5: Run tests — expect pass**

Run: `php artisan test tests/Feature/Ticketing/ProblemTypeEnrichmentTest.php` → Expected: PASS. Then `php artisan migrate:fresh --seed --env=testing` sanity (or the suite's fresh flow) to confirm clean migrate.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_08_13_000001_enrich_ticket_problem_types.php app/Models/TicketProblemType.php tests/Feature/Ticketing/ProblemTypeEnrichmentTest.php
git commit -m "feat: enrich ticket problem types (categories, priority, SLA, checklist link)"
```

### Task 2: Admin form + controller for the new fields

**Files:**
- Modify: `app/Http/Controllers/Ticketing/TicketProblemTypeController.php:22-30`
- Modify: `app/Services/Ticketing/TicketProblemTypeService.php` (the `create`/update path)
- Modify: `resources/views/ticketing/problem-types/index.blade.php`
- Test: `tests/Feature/Ticketing/ProblemTypeEnrichmentTest.php` (add a controller test)

**Interfaces:**
- Consumes: category + priority constant lists (define as `public const CATEGORIES` / `PRIORITIES` on `TicketProblemType` so the view and validation share them).

- [ ] **Step 1: Add shared constants to the model**

In `app/Models/TicketProblemType.php`:

```php
    public const CATEGORIES = ['electrical', 'mechanical', 'hydraulic', 'pneumatic', 'plc_software', 'calibration', 'consumable', 'other'];
    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];
```

- [ ] **Step 2: Write the failing controller test**

Add to `ProblemTypeEnrichmentTest`:

```php
    public function test_store_accepts_and_saves_the_new_fields(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $owner = \App\Models\User::factory()->create();
        $tpl = \App\Models\ChecklistTemplate::create(['name' => 'PM', 'is_active' => true]);

        $this->actingAs($owner)->post(route('admin.ticket-problem-types.store'), [
            'category' => 'pneumatic',
            'name' => 'Air line rupture',
            'description' => 'Hissing at the manifold.',
            'default_priority' => 'urgent',
            'estimated_resolution_hours' => 2,
            'checklist_template_id' => $tpl->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_problem_types', [
            'category' => 'pneumatic', 'default_priority' => 'urgent', 'checklist_template_id' => $tpl->id,
        ]);
    }

    public function test_store_rejects_an_unknown_category(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $owner = \App\Models\User::factory()->create();
        $this->actingAs($owner)->post(route('admin.ticket-problem-types.store'), [
            'category' => 'banana', 'name' => 'x',
        ])->assertSessionHasErrors('category');
    }
```

Run → Expected: FAIL.

- [ ] **Step 3: Update controller validation + persistence**

In `TicketProblemTypeController::store` (and the update path if present):

```php
        $data = $request->validate([
            'category' => ['required', \Illuminate\Validation\Rule::in(\App\Models\TicketProblemType::CATEGORIES)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'default_priority' => ['required', \Illuminate\Validation\Rule::in(\App\Models\TicketProblemType::PRIORITIES)],
            'estimated_resolution_hours' => 'nullable|integer|min:0|max:8760',
            'checklist_template_id' => 'nullable|exists:checklist_templates,id',
        ]);

        $this->service->create($data);
```

Update `TicketProblemTypeService::create()` to persist the full `$data` array (it currently only takes `category`,`name`) — pass the array straight to `TicketProblemType::create($data)`.

- [ ] **Step 4: Update the admin view**

In `resources/views/ticketing/problem-types/index.blade.php`, extend the create form: category `<select>` iterating `TicketProblemType::CATEGORIES` (humanized labels), a `description` textarea, a `default_priority` select from `PRIORITIES`, an `estimated_resolution_hours` number input, and a `checklist_template_id` select populated from active `ChecklistTemplate`s (pass `$templates` from the controller `index()`; add `compact('templates')` there with `ChecklistTemplate::where('is_active',true)->orderBy('name')->get()`). Show the new columns in the list table too (category humanized, priority as an `<x-ui.status-badge>`). Mark required fields with the app's `<span class="text-danger">*</span>` convention.

- [ ] **Step 5: Run tests — expect pass; then commit**

Run: `php artisan test tests/Feature/Ticketing/ProblemTypeEnrichmentTest.php` → PASS.
```bash
git add app/Http/Controllers/Ticketing/TicketProblemTypeController.php app/Services/Ticketing/TicketProblemTypeService.php app/Models/TicketProblemType.php resources/views/ticketing/problem-types/index.blade.php tests/Feature/Ticketing/ProblemTypeEnrichmentTest.php
git commit -m "feat: manage the enriched problem-type fields from the admin panel"
```

### Task 3: Auto-apply the linked checklist template when a ticket is assigned

**Files:**
- Modify: `app/Services/Ticketing/TicketService.php:69-91` (the `assign` method)
- Test: `tests/Feature/Ticketing/TicketChecklistAutoApplyTest.php`

**Interfaces:**
- Consumes: `App\Services\Job\ChecklistTemplateService::applyToJob(ChecklistTemplate $template, Job $job): int` (existing), `$ticket->problemType->checklistTemplate` (from Task 1).

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Ticketing/TicketChecklistAutoApplyTest.php
// Build a ticket whose problem type links an active template with 2 items,
// assign the ticket to a worker, and assert the created job now has 2
// checklist items matching the template's descriptions. Reuse the ticket
// factory/helpers from the existing tests/Feature/Ticketing/* suite for the
// machine/account/problem-type/ticket setup; only the template-link + assign
// assertions below are new.
```

Write the concrete test using the existing ticket-assign test as a template (there is already coverage of `TicketService::assign` creating a job — copy that arrangement, add a linked `ChecklistTemplate` with two `items()`, and assert `$job->checklistItems` count/descriptions after assign). Run → Expected: FAIL.

- [ ] **Step 2: Wire the auto-apply**

In `TicketService::assign`, after `$ticket->job_id = $job->id;` and before saving, inject and call the service. Add `ChecklistTemplateService` to the constructor (constructor-inject, matching how `jobService` is injected), then:

```php
        $template = $ticket->problemType?->checklistTemplate;
        if ($template && $template->is_active) {
            app(\App\Services\Job\ChecklistTemplateService::class)->applyToJob($template, $job);
        }
```

(Use the constructor-injected instance if you add one; `app(...)` is acceptable if constructor churn is undesirable — match the file's existing style.)

- [ ] **Step 3: Run tests — expect pass; commit**

Run: `php artisan test tests/Feature/Ticketing/TicketChecklistAutoApplyTest.php` → PASS.
```bash
git add app/Services/Ticketing/TicketService.php tests/Feature/Ticketing/TicketChecklistAutoApplyTest.php
git commit -m "feat: auto-apply a problem-type's checklist template to the job on assign"
```

### Task 4: Ticket priority, defaulted from the problem type

**Files:**
- Create: `database/migrations/2026_08_13_000002_add_priority_to_tickets.php`
- Modify: `app/Models/Ticket.php` (`$fillable`)
- Modify: `app/Services/Ticketing/TicketService.php` (the ticket-creation path — locate the `create`/`raise` method)
- Modify: ticket show views (`resources/views/ticketing/tickets/show.blade.php`, `resources/views/client/tickets/show.blade.php`) + list views to display the priority badge.
- Test: `tests/Feature/Ticketing/TicketPriorityTest.php`

**Interfaces:**
- Produces: `tickets.priority` (string, default `'medium'`). On ticket creation, priority is copied from `problemType->default_priority`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Ticketing/TicketPriorityTest.php
// Arrange a problem type with default_priority = 'urgent'; create a ticket of
// that type through the same path the client-portal create uses; assert the
// stored ticket's priority === 'urgent'. Reuse existing ticket-create helpers.
```

Run → Expected: FAIL (no `priority` column).

- [ ] **Step 2: Migration**

```php
<?php
// database/migrations/2026_08_13_000002_add_priority_to_tickets.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('priority')->default('medium')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
```

- [ ] **Step 3: Default the priority on creation**

Add `'priority'` to `Ticket::$fillable`. In the ticket-creation method of `TicketService`, set `priority` from the chosen problem type's `default_priority` (fall back to `'medium'` if the type has none). Find the create method (the one the client `POST /portal/tickets` uses) and set it there so both client and admin creation paths get it.

- [ ] **Step 4: Show the priority badge**

Add an `<x-ui.status-badge>` for priority to both ticket show views and both ticket list views (map `urgent→danger, high→warning, medium→secondary, low→light`). Include priority in any ticket sort if the list already sorts.

- [ ] **Step 5: Run tests — expect pass; commit**

Run: `php artisan test tests/Feature/Ticketing/TicketPriorityTest.php` → PASS.
```bash
git add database/migrations/2026_08_13_000002_add_priority_to_tickets.php app/Models/Ticket.php app/Services/Ticketing/TicketService.php resources/views/ticketing/tickets/ resources/views/client/tickets/
git commit -m "feat: ticket priority defaulted from the problem type"
```

---

# PLAN C — Worker "Today at a Glance" Summary Strip

**Context:** A plain Worker (no `jobs.view-all`) lands on `worker.jobs.index` (`JobController::index`, `app/Http/Controllers/Job/JobController.php:29-47`), which passes `$jobs` (their jobs) and `$machines`. The view (`resources/views/worker/jobs/index.blade.php`) opens straight into an `<h4>My Jobs</h4>` + a card list. We add a compact, high-contrast summary band above the list with a greeting and four stat tiles — **To Do**, **In Progress**, **Overdue**, **Completed Today** — all derived from `$jobs` (no new queries). This is my chosen design: four tiles map directly to the statuses a shop-floor worker acts on, in the existing kiosk visual language (big touch targets, `worker-kiosk` styles).

## File Structure
- Modify: `app/Http/Controllers/Job/JobController.php:29-47` — compute a `$summary` array for the worker branch.
- Create: `resources/views/worker/jobs/_summary.blade.php` — the strip partial.
- Modify: `resources/views/worker/jobs/index.blade.php` — include the partial above the list.
- Test: `tests/Feature/Job/WorkerSummaryStripTest.php`

### Task C1: Compute and render the worker summary strip

**Files:**
- Modify: `app/Http/Controllers/Job/JobController.php` (worker branch of `index`)
- Create: `resources/views/worker/jobs/_summary.blade.php`
- Modify: `resources/views/worker/jobs/index.blade.php`
- Test: `tests/Feature/Job/WorkerSummaryStripTest.php`

**Interfaces:**
- Produces: `$summary = ['todo' => int, 'in_progress' => int, 'overdue' => int, 'completed_today' => int]`, passed to `worker.jobs.index`.
- Definitions (derived from the already-loaded `$jobs` collection):
  - `in_progress` = jobs with `status === 'in_progress'`.
  - `overdue` = jobs with a non-null `overdue_flagged_at` and `status !== 'completed'`.
  - `completed_today` = jobs with `status === 'completed'` and `updated_at` on today's date.
  - `todo` = jobs with `status` in `['assigned','on_hold']` (open, not yet started, excluding rejected/completed).

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Job/WorkerSummaryStripTest.php
namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerSummaryStripTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_worker_landing_shows_correct_summary_counts(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        // Use the same Job creation helper the other worker/job tests use so the
        // assigned_to / status / overdue_flagged_at columns are set correctly.
        // Arrange: 2 assigned (todo), 1 in_progress, 1 overdue (flagged, not done),
        // 1 completed today.
        // ... build $jobs for $worker ...

        $res = $this->actingAs($worker)->get(route('jobs.index'));

        $res->assertOk();
        $res->assertSee('In Progress');
        $res->assertSee('Overdue');
        $res->assertSeeText('Completed Today');
        // Assert the computed numbers appear (scope assertions to the summary
        // strip markup, e.g. an id="worker-summary" wrapper, to avoid matching
        // job-card text).
    }
}
```

Fill the arrange block using the existing worker/job test helpers (see `tests/Feature/Job/JobPhotoUploadTest.php` / `WorkerJob*` for how jobs are created for a worker). Run → Expected: FAIL.

- [ ] **Step 2: Compute the summary in the controller**

In `JobController::index`, in the worker branch (before `return view('worker.jobs.index', ...)`):

```php
        $today = now()->toDateString();
        $summary = [
            'todo' => $jobs->whereIn('status', ['assigned', 'on_hold'])->count(),
            'in_progress' => $jobs->where('status', 'in_progress')->count(),
            'overdue' => $jobs->filter(fn ($j) => $j->overdue_flagged_at && $j->status !== 'completed')->count(),
            'completed_today' => $jobs->filter(fn ($j) => $j->status === 'completed' && optional($j->updated_at)->toDateString() === $today)->count(),
        ];

        return view('worker.jobs.index', compact('jobs', 'machines', 'summary'));
```

(Confirm `$jobs` is an Eloquent Collection so `where`/`filter` are in-memory; if it's a paginator or query builder, call `->get()`/adapt.)

- [ ] **Step 3: Create the strip partial**

```blade
{{-- resources/views/worker/jobs/_summary.blade.php --}}
<div id="worker-summary" class="mb-4">
    <h4 class="mb-3">{{ now()->format('H') < 12 ? 'Good morning' : (now()->format('H') < 17 ? 'Good afternoon' : 'Good evening') }}, {{ auth()->user()->name }}</h4>
    <div class="row g-2 text-center">
        @foreach([
            ['label' => 'To Do', 'value' => $summary['todo'], 'variant' => 'secondary', 'icon' => 'ri-inbox-line'],
            ['label' => 'In Progress', 'value' => $summary['in_progress'], 'variant' => 'primary', 'icon' => 'ri-loader-4-line'],
            ['label' => 'Overdue', 'value' => $summary['overdue'], 'variant' => 'danger', 'icon' => 'ri-alarm-warning-line'],
            ['label' => 'Completed Today', 'value' => $summary['completed_today'], 'variant' => 'success', 'icon' => 'ri-checkbox-circle-line'],
        ] as $tile)
        <div class="col-6 col-md-3">
            <div class="card border h-100 mb-0">
                <div class="card-body p-3">
                    <i class="{{ $tile['icon'] }} fs-3 text-{{ $tile['variant'] }}"></i>
                    <div class="fs-1 fw-bold lh-1 my-1">{{ $tile['value'] }}</div>
                    <div class="text-muted">{{ $tile['label'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
```

- [ ] **Step 4: Include it above the list**

In `resources/views/worker/jobs/index.blade.php`, replace the opening `<h4 class="mb-3">My Jobs</h4>` with `@include('worker.jobs._summary')` followed by `<h5 class="mb-3">My Jobs</h5>` (the greeting now heads the page; the list gets a lighter subheading).

- [ ] **Step 5: Run tests — expect pass; commit**

Run: `php artisan test tests/Feature/Job/WorkerSummaryStripTest.php` → PASS.
```bash
git add app/Http/Controllers/Job/JobController.php resources/views/worker/jobs/_summary.blade.php resources/views/worker/jobs/index.blade.php tests/Feature/Job/WorkerSummaryStripTest.php
git commit -m "feat: add a today-at-a-glance summary strip to the worker landing"
```

---

# PLAN D — App-Wide Visual-Consistency Sweep

**Context:** The indigo/navy rebrand (`#4361EE` primary, `#0D1B48` sidebar, `#F7941D` accent) is delivered via a global cascade (`public/build/css/brand-theme.css` + `theme-overrides.blade.php`) that reaches most pages automatically — a scan shows **no** legacy burnt-orange hex left in views and only one inline hardcoded color. So this is not a re-theming job; it's a **consistency audit**: confirm every route-reachable page renders inside the branded shell, every role's login/landing is visually uniform, and no page hand-styles around the brand tokens. Because the exact fixes can't be known until the audit runs, Task D1 is a discovery step that PRODUCES the concrete remediation list; Tasks D2+ execute it. This plan is **manual-verification heavy** (there is no visual-diff harness) — each fix is confirmed by loading the page as each role.

> **Pre-flight note for the executor:** run Task D1 first and bring its inventory back to your human partner before doing D2+, so the remediation scope is agreed. The route `name('login')` currently resolves to `Client\Auth\ClientLoginController::showLoginForm` — confirm during D1 whether staff and client share one login screen or two, since "all user role login consistency" hinges on that.

### Task D1: Consistency audit (discovery — produces the fix list)

**Files:**
- Create: `docs/superpowers/audits/2026-08-12-visual-consistency.md` (the inventory)

- [ ] **Step 1: Inventory every route-reachable page and its layout**

Run and record results:
```bash
# Which layout each view extends
grep -rhoE "@extends\('[^']+'\)" resources/views --include=*.blade.php | sort | uniq -c | sort -rn
# Views NOT extending a known-branded layout (master, master-without-nav, client, worker)
grep -rLE "@extends\('layouts\.(master|master-without-nav|client|worker)'\)" resources/views --include=*.blade.php
# Any hardcoded hex colors in views (should be near-zero; each is a finding)
grep -rnE "#[0-9a-fA-F]{6}" resources/views --include=*.blade.php
# Leftover Velzon demo pages that are not wired to any route (candidates to delete)
grep -rhoE "view\('[^']+'\)|@extends" routes/web.php app/Http/Controllers -r
```
Record in the audit doc: (a) each layout and how many pages use it; (b) every page not on a branded layout, with whether it's route-reachable or dead template cruft; (c) every hardcoded color with file:line; (d) the login/landing screen each role actually sees (Owner, Manager, Account, Worker, Client) with a note on visual divergence.

- [ ] **Step 2: Classify findings into remediation buckets**

In the same doc, group findings under: **(B2) dead template files to delete**, **(B3) hardcoded colors to tokenize**, **(B4) login/landing consistency fixes**, **(B5) per-page shell/component inconsistencies**. Each entry = exact file + the concrete change. Bring this list to the human before executing.

- [ ] **Step 3: Commit the audit**

```bash
git add docs/superpowers/audits/2026-08-12-visual-consistency.md
git commit -m "docs: visual-consistency audit inventory"
```

### Task D2: Remove dead Velzon demo templates (from D1 bucket B2)

- [ ] For each file D1 confirmed is not route-reachable (e.g. `auth-signin-cover`, `auth-404-*`, `auth-signup-*` if unused), delete it, then grep the codebase to confirm nothing references it. Run `php artisan test` (route/smoke tests must still pass). Commit as one change: `chore: remove unused Velzon demo templates`.

### Task D3: Tokenize any hardcoded colors (from bucket B3)

- [ ] For each hardcoded hex D1 found, replace it with the corresponding brand CSS variable / Bootstrap class (`var(--brand-primary)` etc. — match what `theme-overrides.blade.php` defines). Reload the affected page to confirm no visual regression. Commit: `style: replace hardcoded colors with brand tokens`.

### Task D4: Unify login + landing across roles (from bucket B4)

- [ ] Make the login screen every role hits visually identical (one branded login view, or two that share the same brand partial/hero). Confirm each role's post-login landing (Owner→dashboard, Manager/Account→dashboard, Worker→kiosk `/jobs`, Client→portal) carries consistent branding (logo, colors, typography). Manually load each role's login+landing. Commit: `style: unify login and landing branding across all roles`.

### Task D5: Per-page shell/component consistency (from bucket B5)

- [ ] For each divergence D1 listed (a page using a raw table instead of `<x-ui.data-table-card>`, inconsistent page headers, off-brand buttons), align it to the established component/pattern. Keep each commit scoped to one cluster. Manually verify. Final commit(s): `style: align <page> to the standard shell/components`.

> **Whole-plan-D verification:** after D2–D5, load — as each of Owner, Manager, Account, Worker, Client — the login, the landing, and one representative page of each module, confirming a single consistent visual identity. Record the pass in the audit doc.

---

## Self-Review Notes (author)

- **Spec coverage:** A = quotation real-PDF (already done) + auto-print ✅. B = more categories ✅ + richer fields (description/priority/SLA/checklist link) ✅ + the traveler link wired into job creation ✅ + ticket priority ✅. C = worker summary strip (my design) ✅. D = app-wide consistency incl. all-role login ✅.
- **Permissions:** no NEW permissions are introduced (problem-type + quotation reuse existing `checklist-templates.manage` is NOT needed here; problem-type management already gated). So no `RolesAndPermissionsSeeder` change for A–C. Confirm during execution that `admin.ticket-problem-types.*` routes keep their existing gate.
- **Deploy footprint:** Plan B adds two migrations (`ticket_problem_types` enrich, `tickets.priority`) — run `migrate` on deploy. A, C, D need no migration. D may delete files (dead templates) — deploy is a normal code push.
- **Type consistency:** `checklist_template_id` is a `foreignId(...)->constrained()->nullOnDelete()` (matches the FK-type constraint); `applyToJob(ChecklistTemplate, Job): int` signature reused verbatim from the traveler module.
- **Ordering:** Within Plan B, Task 1 → 2 → 3 → 4 (schema before UI before wiring before ticket-priority). Plans A/C/D are order-independent of B and of each other.
